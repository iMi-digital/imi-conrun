<?php

namespace IMI\Util\Console\Helper;

use Symfony\Component\Console\Tester\CommandTester;
use IMI\Contao\Command\PHPUnit\TestCase;
use org\bovigo\vfs\vfsStream;


class ContaoHelperTest extends TestCase
{
    /**
     * @return ContaoHelper
     */
    protected function getHelper()
    {
        $inputMock = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $outputMock = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        return new ContaoHelper($inputMock, $outputMock);
    }

    /**
     * @test
     */
    public function testHelperInstance()
    {
        $this->assertInstanceOf('\IMI\Util\Console\Helper\ContaoHelper', $this->getHelper());
    }

    /**
     * @test
     */
    public function detectContaoInStandardFolder()
    {
        vfsStream::setup('root');
        vfsStream::create(
            array(
                'app' => array(
                    'Mage.php' => ''
                )
            )
        );

        $helper = $this->getHelper();
        $helper->detect(vfsStream::url('root'), array());

        $this->assertEquals(vfsStream::url('root'), $helper->getRootFolder());
        $this->assertEquals(\IMI\Contao\Application::MAGENTO_MAJOR_VERSION_1, $helper->getMajorVersion());
    }

    /**
     * @test
     */
    public function detectContaoInHtdocsSubfolder()
    {
        vfsStream::setup('root');
        vfsStream::create(
            array(
                'htdocs' => array(
                    'app' => array(
                        'Mage.php' => ''
                    )
                )
            )
        );

        $helper = $this->getHelper();

        // vfs cannot resolve relative path so we do 'root/htdocs' etc.
        $helper->detect(
            vfsStream::url('root'),
            array(
                vfsStream::url('root/www'),
                vfsStream::url('root/public'),
                vfsStream::url('root/htdocs'),
            )
        );

        $this->assertEquals(vfsStream::url('root/htdocs'), $helper->getRootFolder());
        $this->assertEquals(\IMI\Contao\Application::MAGENTO_MAJOR_VERSION_1, $helper->getMajorVersion());
    }

    /**
     * @test
     */
    public function detectContaoFailed()
    {
        vfsStream::setup('root');
        vfsStream::create(
            array(
                'htdocs' => array()
            )
        );

        $helper = $this->getHelper();

        // vfs cannot resolve relative path so we do 'root/htdocs' etc.
        $helper->detect(
            vfsStream::url('root')
        );

        $this->assertNull($helper->getRootFolder());
    }

    /**
     * @test
     */
    public function detectContaoInModmanInfrastructure()
    {
        vfsStream::setup('root');
        vfsStream::create(
            array(
                '.basedir' => 'root/htdocs/contao_root',
                'htdocs' => array(
                    'contao_root' => array(
                        'app' => array(
                            'Mage.php' => ''
                        )
                    )
                )
            )
        );

        $helper = $this->getHelper();

        // vfs cannot resolve relative path so we do 'root/htdocs' etc.
        $helper->detect(
            vfsStream::url('root')
        );

        // Verify if this could be checked with more elegance
        $this->assertEquals(vfsStream::url('root/../root/htdocs/contao_root'), $helper->getRootFolder());

        $this->assertEquals(\IMI\Contao\Application::MAGENTO_MAJOR_VERSION_1, $helper->getMajorVersion());
    }

    /**
     * @test
     */
    public function detectContao2InHtdocsSubfolder()
    {
        vfsStream::setup('root');
        vfsStream::create(
            array(
                'htdocs' => array(
                    'app' => array(
                        'autoload.php'  => '',
                        'bootstrap.php' => '',
                    )
                )
            )
        );

        $helper = $this->getHelper();

        // vfs cannot resolve relative path so we do 'root/htdocs' etc.
        $helper->detect(
            vfsStream::url('root'),
            array(
                vfsStream::url('root/www'),
                vfsStream::url('root/public'),
                vfsStream::url('root/htdocs'),
            )
        );

        $this->assertEquals(vfsStream::url('root/htdocs'), $helper->getRootFolder());
        $this->assertEquals(\IMI\Contao\Application::MAGENTO_MAJOR_VERSION_2, $helper->getMajorVersion());
    }
}