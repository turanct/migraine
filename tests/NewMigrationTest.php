<?php

namespace Turanct\Migrations;

use PHPUnit\Framework\TestCase;

final class NewMigrationTest extends TestCase
{
    /**
     * @expectedException \Turanct\Migrations\PleaseProvideValidGroupName
     */
    public function test_it_errors_when_group_is_non_existing()
    {
        $group = 'testGroup';

        $config = $this->getConfig($group);

        $filesystem = $this->getMockBuilder(Filesystem::class)->getMock();
        $filesystem
            ->expects($this->never())
            ->method('touch');

        $clock = $this->getMockBuilder(Clock::class)->getMock();

        $newMigration = new NewMigration($config, $filesystem, $clock);
        $newMigration->create('non-existing-group', '');
    }

    public function test_it_generates_a_file_without_suffix()
    {
        $suffix = '';
        $group = 'testGroup';

        $config = $this->getConfig($group);

        $time = new \DateTimeImmutable('now');
        $clock = $this->getMockBuilder(Clock::class)->getMock();
        $clock->method('getTime')->willReturn($time);

        $filename = "{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}/{$group}/{$time->format('YmdHisv')}.sql";
        $filesystem = $this->getMockBuilder(Filesystem::class)->getMock();
        $filesystem
            ->expects($this->once())
            ->method('touch')
            ->with($this->equalTo($filename));

        $newMigration = new NewMigration($config, $filesystem, $clock);
        $newMigration->create($group, $suffix);
    }

    public function test_it_generates_a_file_with_suffix()
    {
        $suffix = 'migration';
        $group = 'testGroup';

        $config = $this->getConfig($group);

        $time = new \DateTimeImmutable('now');
        $clock = $this->getMockBuilder(Clock::class)->getMock();
        $clock->method('getTime')->willReturn($time);

        $filename = "{$config->getWorkingDirectory()}/{$config->getMigrationsDirectory()}/{$group}/{$time->format('YmdHisv')}-{$suffix}.sql";
        $filesystem = $this->getMockBuilder(Filesystem::class)->getMock();
        $filesystem
            ->expects($this->once())
            ->method('touch')
            ->with($this->equalTo($filename));

        $newMigration = new NewMigration($config, $filesystem, $clock);
        $newMigration->create($group, $suffix);
    }

    /**
     * @param string $group
     *
     * @return Config
     */
    private function getConfig(string $group): Config
    {
        $config = new Config(
            __DIR__,
            'migrations',
            [new Group($group, [new Database('connection', 'user', 'pass')])]
        );

        return $config;
    }
}
