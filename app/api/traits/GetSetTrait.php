<?php

namespace app\api\traits;

trait GetSetTrait
{
    /**
     * @return array
     */
    public function getLoginExcept(): array
    {
        return $this->loginExcept;
    }

    /**
     * @param array $loginExcept
     */
    public function setLoginExcept(array $loginExcept): void
    {
        $this->loginExcept = $loginExcept;
    }
}