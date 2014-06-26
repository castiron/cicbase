# CICBase

### Compatibility ###
* master => TYPO3 6.x
* [TYPO3_4.7.x](https://github.com/castiron/cicbase/tree/TYPO3_4.7.x) => TYPO3 4.7.x

## Features
* [Class by class storage PIDs](#storagePids)
* [File Abstraction Layer](#fal)

<a name="storagePids"></a>
### Class by class storage PIDs
With the normal Typo3/ExtBase setup, `storagePids` are set like this:

```
# Bad
plugin.tx_extName {
    persistence {
        storagePid = 181
    }
}
```
This assumes that all records created and used by your extension will all be stored in the same storage page. **This is a very bad assumption.** There are many times that your extension will use objects from another extension that should be stored in their own page, but are instead stored using your extension's `storagePid`. There are ways to solve it by setting the `storagePid` on each plugin instance by using the Behavior tab. Or somehow manually setting the `storagePid` from within the repository.

In any case, CICBase makes this easier. By just installing the CICBase extension and including the typoscript (don't forget to include the typoscript), you can set a `storagePid` on a class by class basis like this:

```
# Good
config.tx_extbase {
    persistence {
        classes {
            TYPO3\CMS\Extbase\Domain\Model\FrontendUser {
                storagePid = 979
                newRecordStoragePid = 979
            }
            VEND\Jobboard\Domain\Model\JobPost {
                storagePid = 181
                newRecordStoragePid = 181
            }
        }
    }
}
```
#### Notes
* CICBase doesn't implement the `newRecordStoragePid` setting, that's an existing ExtBase setting that you should set along with the new `storagePid` setting detected by CICBase.
* Also, this is completely backwards compatible and the old way of setting `storagePids` is still valid.

<a name="fal"></a>
### File Abstraction Layer
...to be written

