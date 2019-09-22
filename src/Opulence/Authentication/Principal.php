<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Opulence\Authentication;

/**
 * Defines a principal identity
 */
class Principal implements IPrincipal
{
    /** @var string The type of principal this is */
    protected string $type;
    /** @var mixed|null The identity of the principal */
    protected $id;
    /** @var array The list of roles this principal has */
    protected array $roles;

    /**
     * @param string $type The type of principal this is
     * @param mixed $id The identity of the principal
     * @param array $roles The list of roles this principal has
     */
    public function __construct(string $type, $id, array $roles)
    {
        $this->type = $type;
        $this->id = $id;
        $this->roles = $roles;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function hasRole(string $roleName): bool
    {
        return in_array($roleName, $this->roles);
    }
}
