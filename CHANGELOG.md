# Changelog

All notable changes to `guardian-php-sdk` will be documented in this file.
## 1.0.10 2022-10-17
Update http client return - fixed issue where return was failing when body is null.
## 1.0.9 2022-10-17
Fixed issue where an array merge was sometimes merging null.

## 1.0.8 2022-10-14
Merged success and error into one unit so that unit is what gets returned.

## 1.0.5 2022-09-21
Fixed issue where policyid was not being passed into the trustchain.

## 1.0.4 2022-09-16
Fixed issue where an error was thrown due to body being sent on a get request.

## 1.0.3 2022-09-09
Fixed issue where not being able to login to guardian because of json headers.

## 1.0.2 2022-09-06

Error handling is now passed to the application, useful for dealing with 400 errors on the guardian.
## 1.0.1 2022-08-30

- Add Guardian Base Url as a method
## 1.0.0 2022-08-25

- Initial release

