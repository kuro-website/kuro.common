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

    /**
     * @var array
     */
    private $roleId;

    /**
     * @var string
     */
    private $issued;

    /**
     * @var integer
     */
    private $siteId;

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

    public function setRoleId(array $roleIds)
    {
        $this->roleId = $roleIds;
    }

    public function getRoleId(): array
    {
        return $this->roleId;
    }

    public function setIssued(string $issued)
    {
        $this->issued = $issued;
    }

    public function getIssued(): string
    {
        return $this->issued;
    }

    public function setSiteId(int $siteId)
    {
        $this->siteId = $siteId;
    }

    public function getSiteId(): int
    {
        return $this->siteId;
    }
}