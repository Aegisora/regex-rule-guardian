# Aegisora Regex Rule Guardian

[![Latest Version](https://img.shields.io/packagist/v/aegisora/regex-rule-guardian?style=flat-square)](https://packagist.org/packages/aegisora/regex-rule-guardian)
[![Total Downloads](https://img.shields.io/packagist/dt/aegisora/regex-rule-guardian?style=flat-square)](https://packagist.org/packages/aegisora/regex-rule-guardian)
![Code Coverage Badge](./badge.svg)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
![PHPStan Badge](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat)

Regex Rule Guardian provides a simple shortcut for string validation against a regular expression using `aegisora/guardian` and `aegisora/regex-rule`.

It is designed for cases where you want to quickly check whether a value matches a given regex pattern without manually creating validation pipelines.

This package is built on top of:

- [aegisora/guardian](https://github.com/Aegisora/guardian)
- [aegisora/regex-rule](https://github.com/Aegisora/regex-rule)

---

## ✨ Features
- 🔹 Simple shortcut API for `RegexRule`
- 🔹 Validates whether a value matches a regular expression
- 🔹 Uses `aegisora/guardian` internally
- 🔹 Uses `aegisora/regex-rule` internally
- 🔹 Supports custom validation exceptions
- 🔹 Fully compatible with the Aegisora ecosystem
- 🔹 Ready to use out of the box

---

## 📦 Installation

```shell
composer require aegisora/regex-rule-guardian
```

---

## 🚀 Core Concept

This package wraps the common validation flow:

```php
$guardian->check($value, RegexRule::create($pattern), new InvalidValueException());
```

into a dedicated shortcut class:

```php
$regexRuleGuardian->check($value, $pattern, new InvalidValueException());
```

Instead of manually creating `RegexRule` and passing it to `Guardian`, you can use `RegexRuleGuardian` directly.

---

## 🏗️ Basic Usage

```php
use Aegisora\Guardian\Exceptions\GuardianValidationException;
use Aegisora\Guardian\Guardian;
use Aegisora\RuleGuardians\RegexRule\RegexRuleGuardian;

$guardian = new Guardian();

$regexRuleGuardian = new RegexRuleGuardian($guardian);

try {
    $regexRuleGuardian->check('abc123', '/^[a-z0-9]+$/');
    // value matches the pattern
} catch (GuardianValidationException $exception) {
    // value does not match the pattern
}
```

---

## 🧩 Usage with Custom Exception

You may provide your own exception for validation failure.

```php
use Aegisora\Guardian\Guardian;
use Aegisora\RuleGuardians\RegexRule\RegexRuleGuardian;
use App\Exceptions\InvalidValueException;

$guardian = new Guardian();

$regexRuleGuardian = new RegexRuleGuardian($guardian);

$regexRuleGuardian->check('abc 123', '/^[a-z0-9]+$/', new InvalidValueException());
```

If the value does not match the pattern, the provided exception will be thrown.

This is useful when validation errors should have domain-specific meaning.

---

## 🧪 Example in Application Service

```php
use Aegisora\RuleGuardians\RegexRule\RegexRuleGuardian;
use App\Exceptions\InvalidValueException;

final class SlugService
{
    private const SLUG_PATTERN = '/^[a-z0-9-]+$/';

    private RegexRuleGuardian $regexRuleGuardian;

    public function __construct(
        RegexRuleGuardian $regexRuleGuardian
    ) {
        $this->regexRuleGuardian = $regexRuleGuardian;
    }

    /**
     * @param mixed $value
     */
    public function process($value): void
    {
        $this->regexRuleGuardian->check($value, self::SLUG_PATTERN, new InvalidValueException());

        // business logic for a value matching the pattern
    }
}
```

---

## 🚨 Exceptions

This package does not define its own exception types. All errors are raised by the underlying `aegisora/guardian` package.

Both exceptions extend the abstract base class
`Aegisora\Guardian\Exceptions\GuardianException`,
so you can catch every validation error with a single `catch`:

```php
use Aegisora\Guardian\Exceptions\GuardianException;

try {
    $regexRuleGuardian->check($value, $pattern);
} catch (GuardianException $exception) {
    // handles GuardianValidationException and GuardianExecutingRuleException
}
```

### `GuardianValidationException`

Thrown when validation fails and no custom exception is provided.

```php
use Aegisora\Guardian\Exceptions\GuardianValidationException;

try {
    $regexRuleGuardian->check('abc 123', '/^[a-z0-9]+$/');
} catch (GuardianValidationException $exception) {
    echo $exception->getRuleCode(); // "regex_rule"
}
```

### `GuardianExecutingRuleException`

Thrown when the underlying rule execution fails, for example when the value is not a string or the pattern is not a valid regular expression.

`Aegisora\Guardian\Exceptions\GuardianExecutingRuleException`

---

## 🧩 API

### `RegexRuleGuardian::check()`

```php
/**
 * @param mixed $value
 */
public function check(
    $value,
    string $pattern,
    ?\Throwable $exception = null
): void
```

Parameters:
- `$value` *(mixed)* — value to validate against the pattern
- `$pattern` *(string)* — regular expression (including delimiters and flags) the value must match
- `$exception` *(?\Throwable, default `null`)* — optional custom exception thrown on validation failure

Returns `void`. The method communicates results through exceptions only — it returns nothing on success and throws on failure:
- `GuardianValidationException` — validation failed and no custom exception was provided
- `GuardianExecutingRuleException` — the underlying rule failed to execute (e.g. the value is not a string or the pattern is invalid)
- the provided custom exception — validation failed and a custom exception was passed

Example:

```php
$regexRuleGuardian->check('abc123', '/^[a-z0-9]+$/');
```

With custom exception:

```php
$regexRuleGuardian->check('abc 123', '/^[a-z0-9]+$/', new InvalidValueException());
```

---

## 🏛️ Architecture

This package is a small shortcut layer over the Aegisora validation pipeline.

Flow:
1. `RegexRuleGuardian::check()` is called
2. `RegexRule::create($pattern)` is created
3. `Guardian` executes the rule
4. If validation succeeds, execution continues normally
5. If validation fails, custom exception or `GuardianValidationException` is thrown
6. If rule execution fails, `GuardianExecutingRuleException` is thrown

Internal flow:

```text
Value → RegexRuleGuardian → Guardian → RegexRule → Result → Exception
```

---

## 🔗 Related Packages

- [aegisora/guardian](https://github.com/Aegisora/guardian) — validation execution orchestrator
- [aegisora/regex-rule](https://github.com/Aegisora/regex-rule) — rule-based regular expression validation
- [aegisora/rule-contract](https://github.com/Aegisora/rule-contract) — base rule contract and validation result architecture

---

## ⚖️ License

This package is open-source and licensed under the MIT License. See the LICENSE for details.

---

## 🌱 Contributing

Contributions are welcome and greatly appreciated!. See the CONTRIBUTING for details.

---

## 🌟 Support

If you find this project useful, please consider giving it a star on GitHub!

It helps the project grow and motivates further development.
