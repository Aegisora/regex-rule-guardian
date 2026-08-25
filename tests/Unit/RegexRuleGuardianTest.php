<?php

namespace Aegisora\RuleGuardians\RegexRule\Tests\Unit;

use Aegisora\Guardian\Exceptions\GuardianExecutingRuleException;
use Aegisora\Guardian\Exceptions\GuardianValidationException;
use Aegisora\Guardian\Guardian;
use Aegisora\RuleGuardians\RegexRule\RegexRuleGuardian;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

class RegexRuleGuardianTest extends TestCase
{
    private RegexRuleGuardian $guardian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardian = new RegexRuleGuardian(
            new Guardian()
        );
    }

    /**
     * @dataProvider getSuccessfullyCheckProvidedData
     */
    public function testSuccessfullyCheck(
        string $value,
        string $pattern
    ): void {
        $this->expectNotToPerformAssertions();

        $this->guardian->check($value, $pattern);
    }

    public static function getSuccessfullyCheckProvidedData(): array
    {
        return [
            'anchored letters match' => [
                'value' => 'abc',
                'pattern' => '/^[a-z]+$/',
            ],
            'anchored digits match' => [
                'value' => '12345',
                'pattern' => '/^\d+$/',
            ],
            'empty string matches optional pattern' => [
                'value' => '',
                'pattern' => '/^\d*$/',
            ],
            'unanchored pattern matches substring' => [
                'value' => 'abc123',
                'pattern' => '/\d+/',
            ],
            'case insensitive flag matches' => [
                'value' => 'ABC',
                'pattern' => '/^abc$/i',
            ],
            'unicode pattern matches cyrillic' => [
                'value' => 'Привет',
                'pattern' => '/^[а-яё]+$/ui',
            ],
        ];
    }

    /**
     * @dataProvider getFailedCheckProvidedData
     */
    public function testFailedCheck(
        string $value,
        string $pattern,
        ?Throwable $customRuleValidationException,
        string $expectedExceptionClassName
    ): void {
        $this->expectException($expectedExceptionClassName);

        $this->guardian->check($value, $pattern, $customRuleValidationException);
    }

    public static function getFailedCheckProvidedData(): array
    {
        return [
            'anchored letters do not match, custom rule validation exception - null' => [
                'value' => 'abc123',
                'pattern' => '/^[a-z]+$/',
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'anchored letters do not match, custom rule validation exception - not null' => [
                'value' => 'abc123',
                'pattern' => '/^[a-z]+$/',
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'empty string does not match required pattern, custom rule validation exception - null' => [
                'value' => '',
                'pattern' => '/^\d+$/',
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'empty string does not match required pattern, custom rule validation exception - not null' => [
                'value' => '',
                'pattern' => '/^\d+$/',
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'unanchored pattern without substring is invalid, custom rule validation exception - null' => [
                'value' => 'abcdef',
                'pattern' => '/\d+/',
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'unanchored pattern without substring is invalid, custom rule validation exception - not null' => [
                'value' => 'abcdef',
                'pattern' => '/\d+/',
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
            'case sensitive does not match, custom rule validation exception - null' => [
                'value' => 'ABC',
                'pattern' => '/^abc$/',
                'customRuleValidationException' => null,
                'expectedExceptionClassName' => GuardianValidationException::class,
            ],
            'case sensitive does not match, custom rule validation exception - not null' => [
                'value' => 'ABC',
                'pattern' => '/^abc$/',
                'customRuleValidationException' => new CustomRuleException(),
                'expectedExceptionClassName' => CustomRuleException::class,
            ],
        ];
    }

    /**
     * @dataProvider getFailedCheckWithDefaultCustomExceptionProvidedData
     */
    public function testFailedCheckWithDefaultCustomException(
        string $value,
        string $pattern
    ): void {
        $this->expectException(GuardianValidationException::class);

        try {
            $this->guardian->check($value, $pattern);
        } catch (GuardianValidationException $exception) {
            self::assertSame('regex_rule', $exception->getRuleCode());
            throw $exception;
        }
    }

    public static function getFailedCheckWithDefaultCustomExceptionProvidedData(): array
    {
        return [
            'anchored letters do not match' => [
                'value' => 'abc123',
                'pattern' => '/^[a-z]+$/',
            ],
            'empty string does not match required pattern' => [
                'value' => '',
                'pattern' => '/^\d+$/',
            ],
            'unanchored pattern without substring is invalid' => [
                'value' => 'abcdef',
                'pattern' => '/\d+/',
            ],
            'case sensitive does not match' => [
                'value' => 'ABC',
                'pattern' => '/^abc$/',
            ],
        ];
    }

    /**
     * @dataProvider getFailedCheckCauseValueIsNotStringProvidedData
     * @param mixed $value
     */
    public function testFailedCheckCauseValueIsNotStringThrowsGuardianExecutingRuleException(
        $value,
        ?Throwable $customRuleValidationException
    ): void {
        $this->expectException(GuardianExecutingRuleException::class);

        $this->guardian->check($value, '/^[a-z]+$/', $customRuleValidationException);
    }

    public static function getFailedCheckCauseValueIsNotStringProvidedData(): array
    {
        return [
            'value - boolean true, custom rule validation exception - null' => [
                'value' => true,
                'customRuleValidationException' => null,
            ],
            'value - boolean false, custom rule validation exception - null' => [
                'value' => false,
                'customRuleValidationException' => null,
            ],
            'value - zero integer, custom rule validation exception - null' => [
                'value' => 0,
                'customRuleValidationException' => null,
            ],
            'value - positive integer, custom rule validation exception - null' => [
                'value' => 1,
                'customRuleValidationException' => null,
            ],
            'value - negative integer, custom rule validation exception - null' => [
                'value' => -1,
                'customRuleValidationException' => null,
            ],
            'value - zero float, custom rule validation exception - null' => [
                'value' => 0.0,
                'customRuleValidationException' => null,
            ],
            'value - positive float, custom rule validation exception - null' => [
                'value' => 0.01,
                'customRuleValidationException' => null,
            ],
            'value - negative float, custom rule validation exception - null' => [
                'value' => -0.01,
                'customRuleValidationException' => null,
            ],
            'value - null, custom rule validation exception - null' => [
                'value' => null,
                'customRuleValidationException' => null,
            ],
            'value - not empty array, custom rule validation exception - null' => [
                'value' => [123,],
                'customRuleValidationException' => null,
            ],
            'value - empty array, custom rule validation exception - null' => [
                'value' => [],
                'customRuleValidationException' => null,
            ],
            'value - object, custom rule validation exception - null' => [
                'value' => new stdClass(),
                'customRuleValidationException' => null,
            ],
            'value - object, custom rule validation exception - not null' => [
                'value' => new stdClass(),
                'customRuleValidationException' => new CustomRuleException(),
            ],
            'value - callable, custom rule validation exception - null' => [
                'value' => static function () {
                },
                'customRuleValidationException' => null,
            ],
            'value - resource, custom rule validation exception - null' => [
                'value' => tmpfile(),
                'customRuleValidationException' => null,
            ],
        ];
    }

    /**
     * @dataProvider getFailedCheckCauseInvalidPatternProvidedData
     */
    public function testFailedCheckCauseInvalidPatternThrowsGuardianExecutingRuleException(
        string $value,
        string $pattern
    ): void {
        $this->expectException(GuardianExecutingRuleException::class);

        $this->guardian->check($value, $pattern);
    }

    public static function getFailedCheckCauseInvalidPatternProvidedData(): array
    {
        return [
            'invalid pattern - missing delimiters' => [
                'value' => 'abc',
                'pattern' => 'not-a-valid-regex',
            ],
            'invalid pattern - unbalanced group' => [
                'value' => 'abc',
                'pattern' => '/^(abc$/',
            ],
            'runtime failure - invalid utf-8 subject with u flag' => [
                'value' => "\x80\xFF",
                'pattern' => '/^.$/u',
            ],
        ];
    }

    public function testFailedCheckCauseGuardianThrowsGuardianExecutingRuleException(): void
    {
        $this->expectException(GuardianExecutingRuleException::class);

        $guardian = new RegexRuleGuardian(
            $this->getGuardianThrowsExceptionOnCheck(GuardianExecutingRuleException::class)
        );

        $guardian->check('abc', '/^[a-z]+$/');
    }

    public function testFailedCheckCauseGuardianThrowsNotExpectedException(): void
    {
        $this->expectException(Throwable::class);

        $guardian = new RegexRuleGuardian(
            $this->getGuardianThrowsExceptionOnCheck(Throwable::class)
        );

        $guardian->check('abc', '/^[a-z]+$/');
    }

    /**
     * @return Guardian|MockObject
     */
    private function getGuardianThrowsExceptionOnCheck(string $expectedExceptionClass): Guardian
    {
        $guardian = $this->getGuardianMock();

        $guardian
            ->expects(self::once())
            ->method('check')
            ->willThrowException($this->createMock($expectedExceptionClass));

        return $guardian;
    }

    /**
     * @return Guardian|MockObject
     */
    private function getGuardianMock(): Guardian
    {
        /** @var Guardian|MockObject $mock */
        $mock = $this->createMock(Guardian::class);

        return $mock;
    }
}
