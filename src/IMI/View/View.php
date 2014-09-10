<?php

namespace IMI\View;

interface View
{
    public function assign($key, $value);
    public function render();
}