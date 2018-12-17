<?php

namespace App\Controller;

use App\Entity\Reminder;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ReminderController extends ControllerBase
{
    /**
     * Gets all reminders between the given dates.
     *
     * @Route("/reminder/{startDate}/{endDate}", name="reminder_index")
     *
     * @param string $startDate
     * @param string $endDate
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listBetween(string $startDate, string $endDate)
    {
        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate);
        } catch (\Exception $e) {
            return $this->responseError('Invalid start date provided');
        }

        try {
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate);
        } catch (\Exception $e) {
            return $this->responseError('Invalid end date provided');
        }

        $reminders = $this->getRepository()->getAfter($startDate);

        $data = [];
        foreach ($reminders as $reminder) {
            if ($reminder->isRepeating()) {
                $occurrences = $reminder->getOccurrences($startDate, $endDate);
                foreach ($occurrences as $occurrence) {
                    $newReminder = clone $reminder;
                    $newReminder
                      ->setParentReminder($reminder)
                      ->setStartDate($occurrence)
                      ->setEndDate($occurrence)
                      ->setRecurrenceRule(null);
                    $data[$reminder->getStatus()][] = $newReminder->toArray();
                }
            } else {
                $data[$reminder->getStatus()][] = $reminder->toArray();
            }
        }

        $sortData = function ($a, $b) {
            if ($a['start_date'] === $b['start_date']) {
                return $b['amount'] <=> $a['amount'];
            } else {
                return $a['start_date'] <=> $b['start_date'];
            }
        };

        ksort($data);

        $res = [];
        foreach (array_keys($data) as $status) {
            usort($data[$status], $sortData);
            $res = array_merge($res, $data[$status]);
        }


        return $this->responseSuccess($res);
    }

    /**
     * Parses past due reminders.
     *
     * @Route("/reminder/parse-past-due", name="reminder_parse_past_due")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function parsePastDue()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $reminders = $this->getRepository()->getPastDue();
        $today = Carbon::createMidnightDate();
        $count = 0;

        foreach ($reminders as $reminder) {
            $startDate = $reminder->getStartDate();
            // Get all occurrences between the reminder's start date and today -
            // these will become our "past due" occurrences for the reminder.
            $occurrences = $reminder->getOccurrences($startDate, $today);

            if ($occurrences->count()) {
                // Create a new "past due" reminder for each occurrence.
                foreach ($occurrences as $occurrence) {
                    $newReminder = clone $reminder;
                    $newReminder
                      ->setParentReminder($reminder)
                      ->setStartDate($occurrence)
                      ->setEndDate($occurrence)
                      ->setRecurrenceRule(null)
                      ->setStatus('past due');
                    $entityManager->persist($newReminder);
                    // Get the next occurrence after the last past due occurrence and
                    // insert into the database. This will give us our next scheduled
                    // occurrence and become the new "master" occurrence.
                    $next = $reminder->getNextOccurrenceAfter($occurrences->last());
                    $reminder->setStartDate($next);
                    $count++;
                }
            } else {
                $reminder->setStatus('past due');
                $count++;
            }

            $entityManager->persist($reminder);
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true, 'count' => $count]);
    }

    /**
     * @return \App\Repository\ReminderRepository
     */
    protected function getRepository()
    {
        return $this->getDoctrine()->getRepository(Reminder::class);
    }
}