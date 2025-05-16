<?php
// Configuration
$pretalxUrl = 'file.json'; // Replace with your actual Pretalx URL 
// Fetch Pretalx schedule
$json = file_get_contents($pretalxUrl);
if (!$json) {
    die("Failed to fetch Pretalx schedule.");
}
$data = json_decode($json, true);
if (!$data) {
    die("Invalid JSON from Pretalx.");
}
// Prepare iCal header
$ical = "BEGIN:VCALENDAR\r\n";
$ical .= "VERSION:2.0\r\n";
$ical .= "PRODID:-//Pretalx to iCal//EN\r\n";
$ical .= "CALSCALE:GREGORIAN\r\n";
$ical .= "METHOD:PUBLISH\r\n";
// Parse each talk/session
foreach ($data['schedule']['conference']['days'] as $day) {
    foreach ($day['rooms'] as $roomName => $talks) {
        foreach ($talks as $talk) {
            // Skip if start is missing
            if (empty($talk['start'])) {
                continue;
            }
            // Start time
            $start = new DateTime($talk['date']);
            $start->setTimezone(new DateTimeZone('UTC'));
            // If timing is wrong by 1hr, uncomment the line below
            //$start = date_modify($start, "+1 hour");
            // Calculate end time
            if (!empty($talk['end'])) {
                $end = new DateTime($talk['end']);
            } 
            elseif (!empty($talk['duration'])) {
                // Assume duration is "HH:MM"
                list($hours, $minutes) = explode(':', $talk['duration']);
                $end = clone $start;
                $intervalSpec = sprintf('PT%dH%dM', (int)$hours, (int)$minutes);
                $end->add(new DateInterval($intervalSpec));
            }
            else {
                // Default to 1 hour if no end or duration
                $end = clone $start;
                $end->add(new DateInterval('PT1H'));
            }
            $end->setTimezone(new DateTimeZone('UTC'));
            // Extract data
            $uid = md5($talk['url'] ?? uniqid());
            $summary = $talk['title'] ?? 'Untitled';
            $description = $talk['abstract'] ?? '';
            $location = $roomName;
            $url = $talk['url'] ?? '';
            // Add VEVENT
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:$uid\r\n";
           // $ical .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
            $ical .= "DTSTAMP:" . $start->format('Ymd\THis\Z') . "\r\n";
            $ical .= "DTSTART:" . $start->format('Ymd\THis\Z') . "\r\n";
            $ical .= "DTEND:" . $end->format('Ymd\THis\Z') . "\r\n";
            $ical .= "SUMMARY:" . escapeIcalText($summary) . "\r\n";
            $ical .= "DESCRIPTION:" . escapeIcalText($description . "\n\nMore info: " . $url) . "\r\n";
            $ical .= "LOCATION:" . escapeIcalText($location) . "\r\n";
            $ical .= "URL:" . escapeIcalText($url) . "\r\n";
            $ical .= "END:VEVENT\r\n";
        }
    }
}
  $ical .= "END:VCALENDAR\r\n";
  // Output the iCal file
   header('Content-Type: text/calendar; charset=utf-8');
   header('Content-Disposition: attachment; filename="Schedule.ics"');
    echo $ical;
  // Helper to escape iCal text
  function escapeIcalText($text) {
      return preg_replace('/([\,;])/', '\\\\$1', str_replace("\n", "\\n", $text));
  }
?>
