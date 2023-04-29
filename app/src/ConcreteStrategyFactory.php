<?php

require_once "AbstractSecureStrategyFactory.php";
require_once "SecureStrategy.php";
require_once "CreateNewAccountStrategy.php";

class ConcreteStrategyFactory extends AbstractSecureStrategyFactory {
    public function createStrategy($CreateNewAccount) {
        switch ($CreateNewAccount) {
            case 'CreateNewAccount':
                return new CreateNewAccountStrategy();
            default:
                throw new Exception("Invalid strategy name");
        }
    }
}
