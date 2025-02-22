<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection\Compiler;

use Liip\MonitorBundle\DependencyInjection\Compiler\GroupRunnersCompilerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class GroupRunnersCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess(): void
    {
        $defaultGroup = 'groupe_par_défaut';

        $runner = new Definition();
        $this->setDefinition('liip_monitor.runner', $runner);
        $this->setParameter('liip_monitor.default_group', $defaultGroup);
        $this->setParameter('liip_monitor.checks', ['groups' => ['foo' => [], 'baz' => []]]);

        $fooCheck = new Definition();
        $fooCheck->addTag('liip_monitor.check', ['group' => 'foo']);
        $fooCheck->addTag('liip_monitor.check', ['group' => 'foobar']);
        $this->setDefinition('acme.check.foo', $fooCheck);

        $barCheckCollection = new Definition();
        $barCheckCollection->addTag('liip_monitor.check_collection', ['group' => 'bar']);
        $this->setDefinition('acme.check.bar', $barCheckCollection);

        $this->compile();

        $this->assertContainerBuilderHasAlias('liip_monitor.runner', 'liip_monitor.runner_'.$defaultGroup);
        $this->assertContainerBuilderHasService('liip_monitor.runner_'.$defaultGroup);
        $this->assertContainerBuilderHasService('liip_monitor.runner_foo');
        $this->assertContainerBuilderHasService('liip_monitor.runner_foobar');
        $this->assertContainerBuilderHasService('liip_monitor.runner_bar');
        $this->assertContainerBuilderHasService('liip_monitor.runner_baz');
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new GroupRunnersCompilerPass());
    }
}
