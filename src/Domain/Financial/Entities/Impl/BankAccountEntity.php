<?php

declare(strict_types=1);

namespace App\Domain\Financial\Entities\Impl;

use App\Domain\Financial\Entities\BankAccountEntityInterface;
use DateTime;
use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Domain\Products\Repositories\Impl\ProductRepository")
 * @ORM\Table(name="bank_accounts", uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *         name="uniq_bank_agency_account",
 *         columns={"bank", "agency", "account"}
 *     )
 * })
 */
final class BankAccountEntity implements BankAccountEntityInterface, JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    public int $id;

    /**
     * @ORM\Column(type="string", length=100, name="bank")
     */
    public string $bank;

    /**
     * @ORM\Column(type="string", length=10, name="agency")
     */
    public string $agency;

    /**
     * @ORM\Column(type="string", length=20, name="account")
     */
    public string $account;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=25,
     *     columnDefinition="ENUM('corrente','poupanca','salario')"
     *     name="account_type"
     * )
     */
    public string $accountType;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2)
     */
    public float $currentBalance;

    /**
     * @ORM\Column(type="boolean")
     */
    public string $active;

    /**
     * @ORM\Column(type="datetime")
     */
    public DateTime $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    public DateTime $updatedAt;


    public function __construct(
        string $bank,
        string $agency,
        string $account,
        string $accountType,
        float $currentBalance,
        string $active
    ) {
        $this->id = 0;
        $this->bank = $bank;
        $this->agency = $agency;
        $this->account = $account;
        $this->accountType = $accountType;
        $this->currentBalance = $currentBalance;
        $this->active = $active;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'bank' => $this->bank,
            'agency' => $this->agency,
            'account' => $this->account,
            'accountType' => $this->accountType,
            'currentBalance' => $this->currentBalance,
            'active' => $this->active,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}