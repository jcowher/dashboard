<?php

namespace App\Entity;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RRule\RRule;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReminderRepository")
 */
class Reminder
{
    /**
     * @var array
     */
    const STATUSES = ['scheduled', 'pending', 'paid', 'past due'];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint", options={"unsigned":true})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Reminder")
     * @var \App\Entity\Reminder
     */
    private $parentReminder;

    /**
     * @ORM\Column(type="string", length=100)
     * @var string
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $endDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $recurrenceRule;

    /**
     * @ORM\Column(type="string", length=20, options={"default":"scheduled"})
     * @var string
     */
    private $status;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setStatus('scheduled');
    }

    /**
     * Clone
     */
    public function __clone()
    {
        $this->setId(null);
    }

    /**
     * @param int|null $id
     *
     * @return Reminder
     */
    public function setId(?int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param $parentReminder
     *
     * @return Reminder
     */
    public function setParentReminder($parentReminder)
    {
        $this->parentReminder = $parentReminder;

        return $this;
    }

    /**
     * @param string $label
     *
     * @return Reminder
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param string $description
     *
     * @return Reminder
     */
    public function setDescription($description)
    {
        $this->description = !empty($description) ? $description : null;

        return $this;
    }

    /**
     * @param float $amount
     *
     * @return Reminder
     */
    public function setAmount($amount)
    {
        $this->amount = floatval($amount) * 100;

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return Reminder
     */
    public function setStartDate(\DateTime $date)
    {
        $this->startDate = $date->setTime(0, 0, 0);

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return Reminder
     */
    public function setEndDate(?\DateTime $date)
    {
        $this->endDate = ($date instanceof \DateTime) ? $date->setTime(0, 0, 0) : null;

        return $this;
    }

    /**
     * @param string $rule
     *
     * @return Reminder
     */
    public function setRecurrenceRule(?string $rule)
    {
        $this->recurrenceRule = !empty($rule) ? $rule : null;

        return $this;
    }

    /**
     * @param string $status
     *   Either "scheduled", "pending", "paid", or "past due"
     *
     * @return Reminder
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus(string $status)
    {
        if (!in_array($status, self::STATUSES)) {
            throw new \InvalidArgumentException('Invalid status');
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|\App\Entity\Reminder
     */
    public function getParentReminder(): ?Reminder
    {
        return $this->parentReminder;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return null|float
     */
    public function getAmount(): ?float
    {
        return $this->amount ? floatval($this->amount) / 100 : null;
    }

    /**
     * @return null|\DateTime
     */
    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    /**
     * @return null|\DateTime
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * @return null|string
     */
    public function getRecurrenceRule(): ?string
    {
        return $this->recurrenceRule;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isRepeating(): bool
    {
        return !empty($this->recurrenceRule);
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getOccurrences(\DateTime $startDate, \DateTime $endDate): ArrayCollection
    {
        $occurrences = new ArrayCollection();

        $startDate->setTime(0, 0, 0);
        $endDate->setTime(0, 0, 0);

        if ($endDate < $startDate) {
            throw new \InvalidArgumentException('End date can\'t be before start date');
        }

        if (!$this->getStartDate()) {
            throw new \RuntimeException('Reminder does not have a start date');
        }

        if ($this->isRepeating()) {
            $thisEndDate = $this->getEndDate();
            $until = $thisEndDate ? min($thisEndDate->format('Ymd'), $endDate->format('Ymd')) : $endDate->format('Ymd');
            $rrule = $this->getRecurrenceRule().';UNTIL='.$until;
            $dtstart = $this->getStartDate()->format('Y-m-d');
            $rrule_obj = new RRule($rrule, $dtstart);
            foreach ($rrule_obj as $occurrence) {
                $occurrences->add($occurrence);
            }
        }

        return $occurrences;
    }

    /**
     * @param \DateTime $after
     *
     * @return \DateTime|null
     */
    public function getNextOccurrenceAfter(\DateTime $after): ?\DateTime
    {
        $next = null;
        if ($this->isRepeating()) {
            $reminder = clone $this;
            $rrule = $reminder->getRecurrenceRule();
            $rrule_obj = new RRule($rrule, $after);
            $did_one = false;
            foreach ($rrule_obj as $occurrence) {
                if ($did_one) {
                    $next = $occurrence;
                    break;
                }
                $did_one = true;
            }
        }

        return $next;
    }

    /**
     * Gets the reminder as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
          'id' => $this->getId(),
          'parent' => $this->getParentReminder() ? $this->getParentReminder()->toArray() : null,
          'label' => $this->getLabel(),
          'description' => $this->getDescription(),
          'amount' => $this->getAmount(),
          'start_date' => $this->getStartDate() ? $this->getStartDate()->format('Y-m-d') : null,
          'end_date' => $this->getEndDate() ? $this->getEndDate()->format('Y-m-d') : null,
          'recurrence_rule' => $this->getRecurrenceRule(),
          'status' => $this->getStatus(),
        ];
    }

    protected function isValid(\DateTime $startDate, \DateTime $endDate)
    {
        $thisEndDate = $this->getEndDate();
        if ($this->getStartDate() < $startDate) {
            return false;
        }
        if ($thisEndDate && $thisEndDate > $endDate) {
            return false;
        }

        return true;
    }
}