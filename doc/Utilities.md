## Utilities
Inspired by such libraries as Laravel "helpers" or even Underscore.js, there are now several utility classes in cicbase that serve simple, but useful, purposes. The classes are in [`CIC\Utility`](../Classes/Utility). Take a gander at what we’ve got. I’ve found the `CIC\Utility\Arr` methods super helpful. For example:

A lot of times you don’t care whether a variable is set or not, but you need the value if it’s there. To avoid getting any warnings, you better use `isset()`:

```
if (isset($arr[$maybeIndex]) && $arr[$maybeIndex] == ‘foo’) {
  $this->runAway();
}
```

But a nicer way to do this is just:

```
if (Arr::safe($arr, $maybeIndex) == ‘foo’) {
  $this->runAway();
}
```

I know it’s not going to send us to the moon, but it certainly cleans up yer codes.

This also works recursively:

```
$arr = ['I' => ['1' => ['a' => 'this chapter...']]];
$firstChapter = Arr::safe($arr, ['I','1','a']);
```

Or simply:

```
$firstChapter = Arr::safePath($arr, 'I.1.a');
```

Anyway, there’s a ton of goodies in the utility classes and you should add more because you love us.

[back to docs](.)