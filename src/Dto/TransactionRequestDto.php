<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class TransactionRequestDto
{
    #[Assert\NotNull(message: "Amount is required.")]
    #[Assert\Positive(message: "Amount must be a positive value.")]
    #[SerializedName("amount")]
    public float $amount;

    #[Assert\NotNull(message: "Currency is required.")]
    #[Assert\Currency(message: "Invalid currency format.")]
    #[SerializedName("currency")]
    public string $currency;

    #[Assert\NotNull(message: "Card holder name is required.")]
    #[Assert\Length(min: 2, max: 255)]
    #[SerializedName("card_holder_name")]
    public string $cardHolderName;

    #[Assert\NotNull(message: "Card number is required.")]
    #[Assert\Length(min: 13, max: 19)]
    #[Assert\CardScheme(schemes: ["VISA", "MASTERCARD"])]
    #[SerializedName("card_number")]
    public string $cardNumber;

    #[Assert\NotNull(message: "Card expiration month is required.")]
    #[Assert\Length(min: 2, max: 2)]
    #[Assert\Range(min: 1, max: 12)]
    #[SerializedName("card_exp_month")]
    public string $cardExpMonth;

    #[Assert\NotNull(message: "Card expiration year is required.")]
    #[Assert\Length(min: 2, max: 2)]
    #[Assert\Range(min: 21, max: 99)]
    #[SerializedName("card_exp_year")]
    public string $cardExpYear;

    #[Assert\NotNull(message: "Card CVV is required.")]
    #[Assert\Length(min: 3, max: 4)]
    #[SerializedName("card_cvv")]
    public string $cardCvv;
}
