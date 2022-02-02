# Changelog

All notable changes to `migraine` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## NEXT - 2021-02-01

### Added
- The `new` command has been updated and now requires a mandatory action.
  - Actions:
    - `migration`: This indicates you want to create a migration file
    - `seed`: This indicates you want to create a seed file inside the seeds folder
- The `migrate` command now takes an additional optional param: `--seed`
  - Providing this option will also check the seeds or execute the seeds on `--commit`

### Deprecated
- You can no longer create a migration file by only calling the `new` command. It is now required to append the action to this.

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing
