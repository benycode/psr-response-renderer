# Psr 7 response renderer

Psr 7 response renderer

## Table of contents

- [Install](#install)
- [Usage](#usage)

## Install

Via Composer

``` bash
$ composer require benycode/psr-response-renderer
```

## Usage

json renderer:

```php

use Psr\Http\Message\ResponseInterface;

	....
	public function __construct(
        private readonly JsonRenderer $renderer,
    ) {
    }
	....
	
	public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
		return $this->renderer
            ->response($response)
            ->create([
                'message' => 'created',
            ])
            ->withStatus(StatusCodeInterface::STATUS_CREATED)
        ;
	}
```
