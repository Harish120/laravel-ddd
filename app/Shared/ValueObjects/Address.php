<?php

namespace App\Shared\ValueObjects;

class Address extends ValueObject
{
    public function __construct(
        private readonly string $street,
        private readonly string $city,
        private readonly string $state,
        private readonly string $zipCode,
        private readonly string $country,
        private readonly ?string $addressLine2 = null
    ) {}

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function getFullAddress(): string
    {
        $address = $this->street;
        if ($this->addressLine2) {
            $address .= ', '.$this->addressLine2;
        }
        $address .= ', '.$this->city.', '.$this->state.' '.$this->zipCode.', '.$this->country;

        return $address;
    }

    protected function getValues(): array
    {
        return [
            'street' => $this->street,
            'addressLine2' => $this->addressLine2,
            'city' => $this->city,
            'state' => $this->state,
            'zipCode' => $this->zipCode,
            'country' => $this->country,
        ];
    }
}
