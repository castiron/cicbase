## Implicit Fluid Namespaces

Fluid templates allow you to create your own ViewHelpers (VHs). You just have to declare where those VHs can be found:

```
{namespace cic = CIC\MyExt\ViewHelpers}
...
<cic:special arg1="blah" />
```

That will use the `CIC\MyExt\ViewHelpers\SpecialViewHelper` class. That's great! But why do I need to declare the namespace? Well, to avoid conflicts. It's _possible_ that your "cic:special" could refer to the `SpecialViewHelper` from some other extension somewhere else. Is it likely that you're going to run into name collisions? No. Not likely at all.

If the `cicbase` typoscript is loaded in the project, you'll be able to declare your ViewHelper namespaces implicitly in typoscript:

```
plugin.my_ext {
  settings {
    fluidNamespaces {
      cic = CIC\MyExt\ViewHelpers
    }
  }
}
```

Now, you can use `<cic:special>` in all your templates without needing to re-declare the namespace.