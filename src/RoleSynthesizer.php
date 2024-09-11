<?php
namespace AntonioPrimera\BasicPermissions;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

/**
 * Synthesizer for the Role class, used by Livewire
 * to hydrate and dehydrate Role instances.
 */
class RoleSynthesizer extends Synth
{
    public static string $key = 'role';

    static function match($target): bool
    {
        return $target instanceof Role;
    }

    public function dehydrate($target): array
    {
        return [$target->getName(), []];
    }

    public function hydrate($value): Role
    {
        return new Role($value);
    }
}
