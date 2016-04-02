<?php

namespace Surume\Transfer\Socket\Component\Firewall;

use Surume\Transfer\IoServerComponentInterface;

interface SocketFirewallInterface extends IoServerComponentInterface
{
    /**
     * Add an address to the blacklist that will not be allowed to connect to your application.
     *
     * @param string $ip
     * @return SocketFirewallInterface
     */
    public function blockAddress($ip);

    /**
     * Unblock an address so they can access your application again.
     *
     * @param string $ip
     * @return SocketFirewallInterface
     */
    public function unblockAddress($ip);

    /**
     * Check if given $ip is blocked or not.
     *
     * @param string $ip
     * @return bool
     */
    public function isBlocked($ip);

    /**
     * Get an array of all the addresses blocked.
     *
     * @return string[]
     */
    public function getBlockedAddresses();
}
