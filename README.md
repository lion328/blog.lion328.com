# blog.lion328.com

A simple blog system written by me in 2014 to replace an older one. This is unmaintained and only uploaded for archiving purposes.

## Features
- File-based blog system.
- Supported Markdown.
- Full of bugs.

## Installation
### Account creation

Create a new file in `data` and name it as `user-[USERNAME]` where `[USERNAME]` is the username, and write

```
[WRITE_ACCESS]
[HASH]
```

where `[WRITE_ACCESS]` is write access level, set to `1` if you want to give an account write access, and where `[HASH]` is SHA-256 hash of the password prefixed with salt defined in `config.php`.

## Warning

This might have bugs in newly installed blog, mainly because this blog system running by convert from an older system, not freshly empty data ones.

## License

Code release under the MIT license.
