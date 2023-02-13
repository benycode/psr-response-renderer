<?php

declare(strict_types=1);

namespace BenyCode\Psr\ResponseRenderer;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class JsonRenderer
{
    private ResponseInterface $response;

    private Serializer $serializer;

    public function __construct()
    {
        $encoders = [
            new JsonEncoder(),
        ];

        $extractor = new PropertyInfoExtractor([], [
            new PhpDocExtractor(),
            new ReflectionExtractor(),
        ]);

        $normalizers = [
            new ArrayDenormalizer(),
            new ObjectNormalizer(
                null,
                new CamelCaseToSnakeCaseNameConverter(),
                null,
                $extractor
            ),
        ];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function response(ResponseInterface $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function create(
        mixed $data = null,
    ): ResponseInterface {
        $response = $this
            ->response
            ->withHeader('Content-Type', 'application/json')
        ;

        try {
            $serializedData = $this->serializer
                ->serialize(
                    $data,
                    'json',
                    [
                        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                    ],
                );
        } catch (\Throwable $e) {
            $serializedData = json_encode($data);
        }

        $response
                ->getBody()
                ->write((string)$serializedData)
        ;

        return $response;
    }

    public function withErrorMessage(
        string $message,
        int $status = StatusCodeInterface::STATUS_BAD_REQUEST,
    ): ResponseInterface {
        return $this
            ->response($this->response)
            ->create(
                [
                    'message' => $message,
                ]
            )
            ->withStatus($status);
    }
}
