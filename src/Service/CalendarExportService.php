<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Contract\EventInterface;
use App\Entity\Location;
use App\Entity\UserEvent;
use DateTime;
use DateTimeInterface;

final class CalendarExportService
{
    /**
     * @param iterable<EventInterface|UserEvent> $events
     */
    public function exportToIcal(iterable $events): string
    {
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Jamboree//Calendar Export//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";

        foreach ($events as $event) {
            $ical .= $this->prepareForIcalExport($event);
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    private function prepareForIcalExport(EventInterface|UserEvent $event): string
    {
        $eventData = $event instanceof UserEvent ? $event->getEvent() : $event;
        $holdingDate = $eventData->getHoldingDate();

        if (!$holdingDate instanceof DateTimeInterface) {
            return '';
        }

        $start = $holdingDate->format('Ymd');
        $end = (clone $holdingDate)->modify('+1 day')->format('Ymd');
        $created = new DateTime()->format('Ymd\THis\Z');

        $summary = $this->escape($eventData->getName() ?? 'Event');
        $description = $this->escape($eventData->getDescription() ?? '');

        $location = $eventData->getLocation();
        if ($location instanceof Location) {
            $parts = array_filter([
                $location->getVenue(),
                $location->getAddressLine(),
                $location->getCity(),
                $location->getCountry()
            ]);
            $location = $this->escape(implode(', ', $parts));
        }

        $uid = $eventData->getObjectIdentifier() . '@jamboree.app';

        $vevent = "BEGIN:VEVENT\r\n";
        $vevent .= "UID:$uid\r\n";
        $vevent .= "DTSTAMP:$created\r\n";
        $vevent .= "DTSTART;VALUE=DATE:$start\r\n";
        $vevent .= "DTEND;VALUE=DATE:$end\r\n";
        $vevent .= "SUMMARY:$summary\r\n";
        $vevent .= "DESCRIPTION:$description\r\n";

        if (is_string($location)) {
            $vevent .= "LOCATION:$location\r\n";
        }

        $vevent .= "END:VEVENT\r\n";

        return $vevent;
    }

    private function escape(string $text): string
    {
        return str_replace(['\\', ',', ';', "\n", "\r"], ['\\\\', '\\,', '\\;', '\\n', ''], $text);
    }
}
