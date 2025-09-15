<?php
/**
 * Auto-Absent Processing System
 * Automatically marks students as absent for finished events after grace period
 */

require_once __DIR__ . '/index.php';

class AutoAbsentProcessor {
    private $con;
    
    public function __construct() {
        $this->con = getDatabaseConnection();
        if (!$this->con) {
            throw new Exception('Database connection failed');
        }
    }
    
    /**
     * Process auto-absent for all eligible events
     * @param bool $force Force processing even if grace period hasn't expired
     * @return array Results of processing
     */
    public function processAllEvents($force = false) {
        $results = [
            'processed_events' => 0,
            'total_absences_created' => 0,
            'events' => []
        ];
        
        $events = $this->getEligibleEvents($force);
        
        foreach ($events as $event) {
            $eventResult = $this->processEvent($event['event_id'], $force);
            $results['events'][] = $eventResult;
            $results['processed_events']++;
            $results['total_absences_created'] += $eventResult['absences_created'];
        }
        
        return $results;
    }
    
    /**
     * Process auto-absent for a specific event
     * @param int $eventId Event ID to process
     * @param bool $force Force processing even if grace period hasn't expired
     * @return array Result of processing
     */
    public function processEvent($eventId, $force = false) {
        $event = $this->getEventDetails($eventId);
        if (!$event) {
            return ['error' => 'Event not found'];
        }
        
        // Check if already processed
        if ($event['auto_absent_processed'] && !$force) {
            return ['error' => 'Event already processed'];
        }
        
        // Check if event is finished
        if ($event['event_status'] !== 'Finished') {
            return ['error' => 'Event is not finished'];
        }
        
        // Check grace period (unless forced)
        if (!$force && !$this->isGracePeriodExpired($event)) {
            return ['error' => 'Grace period has not expired'];
        }
        
        // Get students who should be marked absent
        $studentsToMarkAbsent = $this->getStudentsToMarkAbsent($event);
        
        if (empty($studentsToMarkAbsent)) {
            $this->markEventAsProcessed($eventId);
            return [
                'event_id' => $eventId,
                'event_title' => $event['title'],
                'absences_created' => 0,
                'message' => 'No students to mark absent'
            ];
        }
        
        // Create absent records
        $absencesCreated = $this->createAbsentRecords($eventId, $studentsToMarkAbsent, $event['abs_penalty']);
        
        // Mark event as processed
        $this->markEventAsProcessed($eventId);
        
        return [
            'event_id' => $eventId,
            'event_title' => $event['title'],
            'absences_created' => $absencesCreated,
            'students_processed' => count($studentsToMarkAbsent),
            'message' => "Successfully created {$absencesCreated} absent records"
        ];
    }
    
    /**
     * Get events eligible for auto-absent processing
     * @param bool $force Include events even if grace period hasn't expired
     * @return array List of eligible events
     */
    private function getEligibleEvents($force = false) {
        $query = "SELECT event_id, title, event_date, end_time, grace_period_hours, auto_absent_processed 
                  FROM events 
                  WHERE event_status = 'Finished' 
                  AND auto_absent_processed = 0";
        
        if (!$force) {
            $query .= " AND (DATE_ADD(DATE_ADD(event_date, INTERVAL TIME_TO_SEC(end_time) SECOND), 
                         INTERVAL grace_period_hours HOUR) <= NOW())";
        }
        
        $query .= " ORDER BY event_date DESC, end_time DESC";
        
        $stmt = $this->con->prepare($query);
        if (!$stmt) {
            throw new Exception('Database error: ' . $this->con->error);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        return $events;
    }
    
    /**
     * Get detailed event information
     * @param int $eventId Event ID
     * @return array|null Event details
     */
    public function getEventDetails($eventId) {
        $query = "SELECT * FROM events WHERE event_id = ?";
        $stmt = $this->con->prepare($query);
        if (!$stmt) {
            throw new Exception('Database error: ' . $this->con->error);
        }
        
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Check if grace period has expired for an event
     * @param array $event Event details
     * @return bool True if grace period has expired
     */
    private function isGracePeriodExpired($event) {
        $eventDateTime = $event['event_date'] . ' ' . $event['end_time'];
        $gracePeriodEnd = date('Y-m-d H:i:s', strtotime($eventDateTime . ' + ' . $event['grace_period_hours'] . ' hours'));
        
        return strtotime($gracePeriodEnd) <= time();
    }
    
    /**
     * Get students who should be marked absent for an event
     * @param array $event Event details
     * @return array List of student IDs
     */
    private function getStudentsToMarkAbsent($event) {
        $eventId = $event['event_id'];
        $eventType = $event['event_type'];
        
        // Base query for students
        $query = "SELECT DISTINCT u.user_id 
                  FROM users u 
                  INNER JOIN students s ON u.user_id = s.student_id 
                  INNER JOIN enrollment e ON s.student_id = e.student_id 
                  WHERE u.role = 'student' 
                  AND u.status = 'Approved' 
                  AND u.account_status = 'active'
                  AND e.status = 'Active'";
        
        // For exclusive events, only include students in assigned sections
        if ($eventType === 'Exclusive') {
            $query .= " AND e.section_id IN (
                        SELECT section_id FROM event_section WHERE event_id = ?
                      )";
        }
        
        // Exclude students who already have attendance records
        $query .= " AND u.user_id NOT IN (
                    SELECT student_id FROM attendance WHERE event_id = ?
                  )";
        
        // Exclude students with approved excuse letters for this event
        $query .= " AND u.user_id NOT IN (
                    SELECT student_id FROM excuse_letters 
                    WHERE event_id = ? AND status = 'Approved'
                  )";
        
        $stmt = $this->con->prepare($query);
        if (!$stmt) {
            throw new Exception('Database error: ' . $this->con->error);
        }
        
        if ($eventType === 'Exclusive') {
            $stmt->bind_param("iii", $eventId, $eventId, $eventId);
        } else {
            $stmt->bind_param("ii", $eventId, $eventId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row['user_id'];
        }
        
        return $students;
    }
    
    /**
     * Create absent records for students
     * @param int $eventId Event ID
     * @param array $studentIds List of student IDs
     * @param int $penalty Penalty amount
     * @return int Number of records created
     */
    private function createAbsentRecords($eventId, $studentIds, $penalty) {
        if (empty($studentIds)) {
            return 0;
        }
        
        $created = 0;
        $insertQuery = "INSERT INTO attendance (student_id, event_id, check_in_time, remark, penalty) 
                        VALUES (?, ?, NOW(), 'Absent', ?)";
        
        $stmt = $this->con->prepare($insertQuery);
        if (!$stmt) {
            throw new Exception('Database error: ' . $this->con->error);
        }
        
        foreach ($studentIds as $studentId) {
            $stmt->bind_param("iii", $studentId, $eventId, $penalty);
            if ($stmt->execute()) {
                $created++;
            }
        }
        
        return $created;
    }
    
    /**
     * Mark event as processed
     * @param int $eventId Event ID
     */
    private function markEventAsProcessed($eventId) {
        $query = "UPDATE events SET auto_absent_processed = 1 WHERE event_id = ?";
        $stmt = $this->con->prepare($query);
        if (!$stmt) {
            throw new Exception('Database error: ' . $this->con->error);
        }
        
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
    }
    
    /**
     * Update attendance from Absent to Excused when excuse is approved
     * @param int $studentId Student ID
     * @param int $eventId Event ID
     * @return bool Success status
     */
    public function updateAbsentToExcused($studentId, $eventId) {
        $query = "UPDATE attendance 
                  SET remark = 'Excused', penalty = 0 
                  WHERE student_id = ? AND event_id = ? AND remark = 'Absent'";
        
        $stmt = $this->con->prepare($query);
        if (!$stmt) {
            throw new Exception('Database error: ' . $this->con->error);
        }
        
        $stmt->bind_param("ii", $studentId, $eventId);
        return $stmt->execute();
    }
    
    /**
     * Get grace period information for an event
     * @param array $event Event details
     * @return array Grace period information
     */
    public function getGracePeriodInfo($event) {
        $eventDateTime = $event['event_date'] . ' ' . $event['end_time'];
        $gracePeriodEnd = date('Y-m-d H:i:s', strtotime($eventDateTime . ' + ' . $event['grace_period_hours'] . ' hours'));
        $now = time();
        $graceEndTime = strtotime($gracePeriodEnd);
        
        $expired = $graceEndTime <= $now;
        $remainingSeconds = $graceEndTime - $now;
        
        if ($expired) {
            $remainingTime = 'Grace period has expired';
        } else {
            $hours = floor($remainingSeconds / 3600);
            $minutes = floor(($remainingSeconds % 3600) / 60);
            
            if ($hours > 0) {
                $remainingTime = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' and ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            } else {
                $remainingTime = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            }
        }
        
        return [
            'expired' => $expired,
            'remaining_time' => $remainingTime,
            'grace_period_hours' => $event['grace_period_hours'],
            'event_end_time' => $eventDateTime,
            'grace_period_end' => $gracePeriodEnd
        ];
    }
    
    /**
     * Get events that are ready for auto-absent processing
     * @return array List of events ready for processing
     */
    public function getEventsReadyForProcessing() {
        $query = "SELECT event_id, title, event_date, end_time, grace_period_hours,
                         DATE_ADD(DATE_ADD(event_date, INTERVAL TIME_TO_SEC(end_time) SECOND), 
                         INTERVAL grace_period_hours HOUR) as grace_period_end
                  FROM events 
                  WHERE event_status = 'Finished' 
                  AND auto_absent_processed = 0
                  AND (DATE_ADD(DATE_ADD(event_date, INTERVAL TIME_TO_SEC(end_time) SECOND), 
                      INTERVAL grace_period_hours HOUR) <= NOW())
                  ORDER BY event_date DESC, end_time DESC";
        
        $stmt = $this->con->prepare($query);
        if (!$stmt) {
            throw new Exception('Database error: ' . $this->con->error);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        return $events;
    }
}
?>
