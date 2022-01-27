<?php

namespace Turanct\Migraine;

use PHPUnit\Framework\TestCase;

final class NewSeedTest extends TestCase
{
    public function test_it_errors_when_group_is_non_existing()
    {
        $this->expectException(PleaseProvideValidGroupName::class);

        $group = 'testGroup';

        $config = $this->getConfig($group);

        $filesystem = $this->getMockBuilder(Filesystem::class)->getMock();
        $filesystem
            ->expects($this->never())
            ->method('touch');

        $clock = $this->getMockBuilder(Clock::class)->getMock();

        $NewSeed = new NewSeed($config, $filesystem, $clock);
        $NewSeed->create('non-existing-group', '');
    }

    public function test_it_generates_a_file_without_suffix()
    {
        $suffix = '';
        $group = 'testGroup';

        $config = $this->getConfig($group);

        $time = new \DateTimeImmutable('now');
        $clock = $this->getMockBuilder(Clock::class)->getMock();
        $clock->method('getTime')->willReturn($time);

        $filename = "{$config->get()->getWorkingDirectory()}/{$config->get()->getMigrationsDirectory()}/{$group}/seeds/{$time->format('YmdHisv')}.sql";
        $filesystem = $this->getMockBuilder(Filesystem::class)->getMock();
        $filesystem
            ->expects($this->once())
            ->method('touch')
            ->with($this->equalTo($filename));

        $NewSeed = new NewSeed($config, $filesystem, $clock);
        $NewSeed->create($group, $suffix);
    }

    public function test_it_generates_a_file_with_suffix()
    {
        $suffix = 'migration';
        $group = 'testGroup';

        $config = $this->getConfig($group);

        $time = new \DateTimeImmutable('now');
        $clock = $this->getMockBuilder(Clock::class)->getMock();
        $clock->method('getTime')->willReturn($time);

        $filename = "{$config->get()->getWorkingDirectory()}/{$config->get()->getMigrationsDirectory()}/{$group}/seeds/{$time->format('YmdHisv')}-{$suffix}.sql";
        $filesystem = $this->getMockBuilder(Filesystem::class)->getMock();
        $filesystem
            ->expects($this->once())
            ->method('touch')
            ->with($this->equalTo($filename));

        $NewSeed = new NewSeed($config, $filesystem, $clock);
        $NewSeed->create($group, $suffix);
    }

    /**
     * @param string $group
     *
     * @return GetConfig
     */
    private function getConfig(string $group): GetConfig
    {
        $config = new Config(
            __DIR__,
            'migrations',
            new LogStrategyJson('logs.json'),
            [new Group($group, [new Database('connection', 'user', 'pass')])]
        );

        $getConfig = $this->getMockBuilder(GetConfig::class)->getMock();
        $getConfig->method('get')->willReturn($config);

        return $getConfig;
    }
}
