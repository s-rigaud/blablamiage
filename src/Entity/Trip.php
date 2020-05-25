<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TripRepository")
 */
class Trip
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @var string A "Y-m-d H:i:s" formatted value
     */
    private $start;

    /**
     * @ORM\Column(type="datetime")
     * @var string A "Y-m-d H:i:s" formatted value
     * @Assert\Expression(
     *          "value > this.getStart()",
     *          message="The trip needs to end after its start !"
     * )
     */
    private $end;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min=1, max=60)
     * @var integer
     */
    private $maxSeats = 1;

    /**
     * @ORM\Column(type="float")
     * @Assert\Range(min=3, max=200)
     * @var float
     */
    private $seatPrice;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     * @ORM\JoinColumn(nullable=false)
     * @var City
     */
    private $fromC;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Expression("not (value == this.getFromC())", message="The trip can not end where it started !")
     * @var City
     */
    private $toC;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    private $driver;

    /**
     * @ORM\Column(type="datetime")
     * @var string A "Y-m-d H:i:s" formatted value
     */
    private $created;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="trip", cascade={"persist", "remove"})
     * @var string
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Booking", mappedBy="trip", cascade={"persist", "remove"})
     * @var array
     */
    private $bookings;

    public function __construct(){
        $this->start = new \Datetime();
        $this->end = new \Datetime();
        $this->created = new \Datetime();
        $this->comments = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getMaxSeats(): ?int
    {
        return $this->maxSeats;
    }

    public function setMaxSeats(int $maxSeats): self
    {
        $this->maxSeats = $maxSeats;

        return $this;
    }

    public function getSeatPrice(): ?float
    {
        return $this->seatPrice;
    }

    public function setSeatPrice(float $seatPrice): self
    {
        $this->seatPrice = $seatPrice;

        return $this;
    }

    public function getFromC(): ?City
    {
        return $this->fromC;
    }

    public function setFromC(?City $fromC): self
    {
        $this->fromC = $fromC;

        return $this;
    }

    public function getToC(): ?City
    {
        return $this->toC;
    }

    public function setToC(?City $toC): self
    {
        $this->toC = $toC;

        return $this;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function __toString(){
        return 'Trip from '.$this->fromC.' to '.$this->toC.' on '.$this->start->format('Y-m-d H:i:s').' by '.$this->driver->getSurname();
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setTrip($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getTrip() === $this) {
                $comment->setTrip(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setTrip($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
            // set the owning side to null (unless already changed)
            if ($booking->getTrip() === $this) {
                $booking->setTrip(null);
            }
        }

        return $this;
    }
}
