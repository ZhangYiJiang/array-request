<?php


use Illuminate\Http\Request;
use ZhangYiJiang\ArrayRequest\Middleware;

class MiddlewareTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Middleware
     */
    protected $middleware;

    /**
     * @var Closure
     */
    protected $next;

    protected $requestData = [
        'flat' => [
            [['name' => 'Twilight',]],
            [['color' => 'purple',]],
            [['name' => 'Applejack',]],
            [['color' => 'orange',]],
            [['name' => 'Rarity',]],
            [['color' => 'white',]],
        ],
        'formatted' => [
            ['name' => 'Twilight', 'color' => 'purple'],
            ['name' => 'Applejack', 'color' => 'orange'],
            ['name' => 'Rarity', 'color' => 'white'],
        ],
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->middleware = new Middleware();
        $this->next = function(Request $request) {
            return $request;
        };
    }

    public function testArrayInput()
    {
        $result = $this->runMiddleware([
            'ponies' => $this->requestData['flat'],
        ]);

        $this->assertEquals($this->requestData['formatted'], $result->ponies);
    }

    public function testPlainArray()
    {
        $data = [
            'Celestia',
            'Luna',
            'Cadance',
        ];

        $result = $this->runMiddleware(['princesses' => $data,]);
        $this->assertEquals($data, $result->princesses);
    }

    public function testAlreadyFormatted()
    {
        $result = $this->runMiddleware([
            'ponies' => $this->requestData['formatted'],
        ]);

        $this->assertEquals($this->requestData['formatted'], $result->ponies);
    }

    public function testIgnore()
    {
        $result = $this->runMiddleware([
            'ponies' => $this->requestData['flat'],
            'ignored1' => $this->requestData['flat'],
            'ignored2' => $this->requestData['flat'],
        ], ['ignored1', 'ignored2']);

        $this->assertEquals($this->requestData['flat'], $result->ignored1);
        $this->assertEquals($this->requestData['flat'], $result->ignored2);
        $this->assertEquals($this->requestData['formatted'], $result->ponies);
    }

    public function testNonArrayInput()
    {
        $data = [
            'integer' => 12,
            'email' => 'hello@example.com',
            'text' => 'Laravel is built with testing in mind. In fact, support for testing with PHPUnit 
            is included out of the box, and a phpunit.xml file is already setup for your application. 
            The framework also ships with convenient helper methods allowing you to expressively',
        ];

        $result = $this->runMiddleware($data);
        $this->assertEquals($data, $result->all());
    }

    /**
     * @param array $data
     * @return Request
     */
    protected function runMiddleware(array $data, array $ignored = [])
    {
        $request = new Request();
        $request->replace($data);
        return $this->middleware->handle($request, $this->next, ...$ignored);
    }
}
