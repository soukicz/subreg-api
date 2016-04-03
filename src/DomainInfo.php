<?php
namespace Soukicz\SubregApi;

class DomainInfo {
    /**
     * @var string
     */
    protected $name;
    /**
     * @var \DateTime
     */
    protected $expiration;

    /**
     * @param string $name
     * @return DomainInfo
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @param \DateTime $expiration
     * @return DomainInfo
     */
    public function setExpiration(\DateTime $expiration) {
        $this->expiration = $expiration;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return \DateTime
     */
    public function getExpiration() {
        return $this->expiration;
    }

}
