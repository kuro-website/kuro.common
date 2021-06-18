<?php
namespace kuro\dto;

/**
 * AdminDTO
 */
class AdminDTO
{
    /**
     * @var integer
     */
    private $adminId;

    /**
     * @var string
     */
    private $username;

    /**
     * @var integer
     */
    private $rank;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $mobile;

    public function setAdminId(int $adminId)
    {
        $this->adminId = $adminId;
    }

    public function getAdminId(): int
    {
        return (int)$this->adminId;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setRank(int $rank)
    {
        $this->rank = $rank;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setMobile(string $mobile)
    {
        $this->mobile = $mobile;
    }

    public function getMobile(): string
    {
        return $this->mobile;
    }
}