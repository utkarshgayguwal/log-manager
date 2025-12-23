<?php

namespace UtkarshGayguwal\LogManagement\Filters;

use Carbon\Carbon;
use Kblais\QueryFilter\QueryFilter;

class LogFilter extends QueryFilter
{
    public function clientId($value)
    {
        return $this->where('client_id', $value);
    }

    public function logType($value)
    {
        return $this->where('log_type', $value);
    }

    public function action($value)
    {
        return $this->where('action', $value);
    }

    public function moduleId($value)
    {
        return $this->where('module_id', $value);
    }

    public function date($value)
    {
        return $this->whereDate('created_at', $value);
    }

    public function search($value)
    {
        return $this->where(function ($query) use ($value) {
            $query->where('title', 'LIKE', "%{$value}%")
                ->orWhere('description', 'LIKE', "%{$value}%");
        });
    }

    public function createdBy($value){
        return $this->whereHas('user', function($query) use ($value){
            $query->where('id', $value);
        });
    }

    public function dateFormater($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }

    public function createdAt($value)
    {
        $dates = !is_array($value) ? explode(',', $value) : $value;

        $validDates = array_filter($dates, function ($date) {
            return $this->isValidDate($date);
        });

        if (empty($validDates)) {
            return $this->where('id', 0);
        }

        // Convert all dates to full timestamp format (Y-m-d H:i:s) at the beginning
        $formattedDates = [];
        $isStartDateOnly = false;

        // Check if start date is date-only (00:00) to determine conversion logic
        if (!empty($validDates)) {
            $startDateString = trim($validDates[0]);
            $isStartDateOnly = substr($startDateString, -5) === '00:00';
        }

        foreach ($validDates as $index => $dateString) {
            $isEndDate = (count($validDates) === 2 && $index === 1);
            $formattedDates[] = $this->formatToTimestamp(trim($dateString), $isEndDate, $isStartDateOnly);
        }

        $startTimestamp = $formattedDates[0];
        $endTimestamp = $formattedDates[1];

        // Check if start date has time 00:00 (determines filtering logic)
        if ($this->isDateOnly($startTimestamp)) {
            $startDate = Carbon::parse($startTimestamp);
            $endDate = Carbon::parse($endTimestamp);

            // For date filtering, include entire days (00:00:00 to 23:59:59)
            return $this->whereDate('created_at', '>=', $startDate->toDateString())
                        ->whereDate('created_at', '<=', $endDate->toDateString());
        } else {
            // Use exact timestamp range matching
            return $this->where('created_at', '>=', $startTimestamp)
                        ->where('created_at', '<=', $endTimestamp);
        }
    }

    protected function isValidDate($date)
    {
        try {
            Carbon::createFromFormat('Y-m-d H:i', trim($date));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Convert Y-m-d H:i format to Y-m-d H:i:s format
     */
    protected function formatToTimestamp($dateString, $isEndDate = false, $isStartDateOnly = false)
    {
        $date = Carbon::createFromFormat('Y-m-d H:i', $dateString);

        if ($isStartDateOnly) {
            // Date-only filtering: start at 00:00:00, end at 23:59:59
            if ($isEndDate) {
                $date->hour(23)->minute(59)->second(59);
            } else {
                $date->second(0);
            }
        } else {
            // Time-specific filtering: add seconds (00 for start, 59 for end)
            if ($isEndDate) {
                $date->second(59);
            } else {
                $date->second(0);
            }
        }

        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Check if the given timestamp has time as 00:00:00
     * This indicates a date-only filter
     */
    protected function isDateOnly($timestamp)
    {
        // Check if the timestamp ends with "00:00:00"
        return substr($timestamp, -8) === '00:00:00';
    }
}