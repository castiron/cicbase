## AbstractTask and InjectionService

In ExtBase, tasks do not come with the magic injections and stuff that you normally have in a controller or view helper. It's usually a roll-your-own kinda thing. But no more.

To get injections (anywhere) you can easily invoke the [InjectionService](../Classes/Service/InjectionService.php):

```
$injectionService = GeneralUtility::makeInstance('CIC\Cicbase\Service\InjectionService');
$injectionService->doInjection($this);
```

This uses the tools provided by ExtBase to inject things in the same ways you expect it to in other places around the extension. That's really handy, right?

Even more handy, you can extend [AbstractTask](../Classes/Scheduler/AbstractTask.php) and call `parent::initialize()` to get the injections as well as to get your extension typoscript settings. **You _must_ pass in the extension and plugin names.**

```
public function execute() {
	parent::initialize('myext', 'default');

	// ...
}
```

[back to docs](.)