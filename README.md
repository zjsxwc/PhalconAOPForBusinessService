#usage

first DI injects the ServiceManager, then in the controller use the AOP.
$someService = $this->serviceManager->getService('SomeService');
$someService->create(...);