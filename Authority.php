<?php
/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2016 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */
namespace Opulence\Authorization;

use Opulence\Authorization\Privileges\IPrivilegeRegistry;
use Opulence\Authorization\Roles\IRoles;

/**
 * Defines the authority
 */
class Authority implements IAuthority
{
    /** @var int|string The Id of the current user */
    private $userId = -1;
    /** @var mixed The current user */
    private $user = null;
    /** @var IPrivilegeRegistry The privilege registry */
    private $privilegeRegistry = null;
    /** @var IRoles The roles */
    private $roles = null;

    /**
     * @param int|string $userId The Id of the current user
     * @param mixed $user The current user
     * @param IPrivilegeRegistry $privilegeRegistry The privilege registry
     * @param IRoles $roles The roles
     */
    public function __construct($userId, $user, IPrivilegeRegistry $privilegeRegistry, IRoles $roles)
    {
        $this->userId = $userId;
        $this->user = $user;
        $this->privilegeRegistry = $privilegeRegistry;
        $this->roles = $roles;
    }

    /**
     * @inheritdoc
     */
    public function can(string $privilege, ...$arguments) : bool
    {
        $requiredRoles = $this->privilegeRegistry->getRoles($privilege);

        // If our user has at least one of the required roles
        if (
            $requiredRoles !== null
            && count(array_intersect($requiredRoles, $this->roles->getRolesForUser($this->userId))) > 0
        ) {
            return true;
        }

        if (($callback = $this->privilegeRegistry->getCallback($privilege)) === null) {
            return false;
        }

        return call_user_func($callback, $this->user, ...$arguments);
    }

    /**
     * @inheritdoc
     */
    public function cannot(string $privilege, ...$arguments) : bool
    {
        return !$this->can($privilege, ...$arguments);
    }
}