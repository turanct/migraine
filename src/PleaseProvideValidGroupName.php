<?php

namespace Turanct\Migrations;

final class PleaseProvideValidGroupName extends \Exception
{
    /**
     * @var string[]
     */
    private $validGroupNames = [];

    /**
     * @param string[] $validGroupNames
     */
    public static function fromList(array $validGroupNames): PleaseProvideValidGroupName
    {
        $exception = new static();
        $exception->validGroupNames = $validGroupNames;

        return $exception;
    }

    public function getList(): array
    {
        return $this->validGroupNames;
    }
}
