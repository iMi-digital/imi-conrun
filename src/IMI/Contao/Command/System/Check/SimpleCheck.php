<?php

namespace IMI\Contao\Command\System\Check;

interface SimpleCheck
{
    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results);
}