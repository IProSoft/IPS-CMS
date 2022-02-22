<?php

namespace TranslatedException;

/**
 * @covers TranslatedException\TranslatedException
 */
class TranslatedExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testTranslateMessage()
    {
        TranslatedException::init();
        TranslatedException::init();
        TranslatedException::addResourceDir(__DIR__.'/Fixtures/translations');

        $exception = new TranslatedException('test', 'hello.%cp_name%', ['%cp_name%' => 'foo']);
        $this->assertEquals('Hello foo!', $exception->getMessage());
        $this->assertNotEquals('Hello foo!', $exception->getVerboseMessage());

        $exception = new TranslatedException('test', 'hello.%cp_name%', ['%cp_name%' => sprintf('%0100d', 1)]);
        $this->assertTrue(60 > strlen($exception->getMessage()));
        $this->assertTrue(60 < strlen($exception->getVerboseMessage()));

        $exception = new TranslatedException('test', 'eat.apples.%cp_count%', ['%cp_count%' => 0], 0);
        $this->assertEquals('I eat no apple.', $exception->getMessage());
        $this->assertNotEquals('I eat no apple.', $exception->getVerboseMessage());

        $exception = new TranslatedException('test', 'eat.apples.%cp_count%', ['%cp_count%' => 1], 1);
        $this->assertEquals('I eat one apple.', $exception->getMessage());
        $this->assertNotEquals('I eat one apple.', $exception->getVerboseMessage());

        $exception = new TranslatedException('test', 'eat.apples.%cp_count%', ['%cp_count%' => 5], 5);
        $this->assertEquals('I eat 5 apples.', $exception->getMessage());
        $this->assertNotEquals('I eat 5 apples.', $exception->getVerboseMessage());
    }
}
