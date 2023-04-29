<?php

require_once "AbstractSecureStrategyFactory.php";
require_once "SecureStrategy.php";

class SecureContext {
    private $strategyFactory;
    private$CreateNewAccount;

    public function __construct(AbstractSecureStrategyFactory $strategyFactory) {
        $this->strategyFactory = $strategyFactory;
    }

    public function setStrategy($CreateNewAccount) {
        $this->strategy = $this->strategyFactory->createStrategy($CreateNewAccount);
    }

    public function executeStrategy($params) {
        if ($this->strategy instanceof SecureStrategy) {
            return $this->strategy->execute($params);
        } else {
            throw new Exception("Invalid strategy");
        }
    }
}
