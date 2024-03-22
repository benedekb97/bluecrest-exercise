<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Enums\TaskStatus;
use DateInterval;
use DateTime;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;

class TasksTest extends TestCase
{
    private static Generator $faker;

    private const TASK_STATUSES = [
        TaskStatus::DRAFT->value,
        TaskStatus::IN_PROGRESS->value,
        TaskStatus::COMPLETED->value,
        TaskStatus::FAILED->value,
    ];

    public function __construct(string $name)
    {
        parent::__construct($name);

        self::$faker = Factory::create();
    }

    public function testListing(): void
    {
        $this->authenticate();

        $this->sendGet('/api/tasks');

        $this->assertStatusOk();

        $response = $this->getResponseArray();

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('links', $response);
        $this->assertArrayHasKey('meta', $response);

        $data = $response['data'];

        $this->assertNotEmpty($data);

        $task = reset($data);

        $this->assertTaskStructure($task);
    }

    /** @dataProvider taskCreationDataProvider */
    public function testCreation(array $taskData, array $validationErrors): void
    {
        $this->authenticate();

        $this->sendJsonPost('/api/tasks', $taskData);

        $response = $this->getResponseArray();

        if (empty($validationErrors)) {
            $this->assertStatusCreated();

            $this->assertTaskStructure($response);
        } else {
            $this->assertStatusUnprocessable();

            $this->assertValidationErrorsMatch($response, $validationErrors);
        }
    }

    public function testShow(): void
    {
        $this->authenticate();

        $task = $this->getRandomTask();

        $this->sendGet(sprintf('/api/tasks/%d', $task['id']));

        $taskResponse = $this->getResponseArray();

        $this->assertArrayHasKey('data', $taskResponse);

        $this->assertTaskStructure($taskResponse['data']);
    }

    /** @dataProvider taskUpdateDataProvider */
    public function testUpdate(array $updateData, array $validationErrors): void
    {
        $this->authenticate();

        $task = $this->getRandomTask();

        $this->sendJsonPatch(sprintf('/api/tasks/%d', $task['id']), $updateData);

        $taskResponse = $this->getResponseArray();

        if (empty($validationErrors)) {
            $this->assertStatusOk();

            $this->assertArrayHasKey('data', $taskResponse);

            $this->assertTaskStructure($taskResponse['data']);

            $this->assertUpdateCompleted($taskResponse['data'], $updateData, $task);
        } else {
            $this->assertStatusUnprocessable();

            $this->assertValidationErrorsMatch($taskResponse, $validationErrors);
        }
    }

    public function testDelete(): void
    {
        $this->authenticate();

        $task = $this->getRandomTask();

        $this->sendDelete($url = sprintf('/api/tasks/%d', $task['id']));

        $this->assertStatusNoContent();

        $this->sendGet($url);

        $this->assertStatusNotFound();
    }

    public static function taskCreationDataProvider(): array
    {
        return [
            'All required data' => [
                [
                    'title' => self::$faker->sentence,
                    'description' => self::generateDescription(),
                    'status' => self::generateStatus(),
                    'due_date' => self::generateDueDate(),
                ],
                [],
            ],
            'Missing title' => [
                [
                    'description' => self::generateDescription(),
                    'status' => self::generateStatus(),
                    'due_date' => self::generateDueDate(),
                ],
                ['title'],
            ],
            'Invalid status' => [
                [
                    'title' => self::$faker->sentence,
                    'description' => self::generateDescription(),
                    'status' => 'ready',
                    'due_date' => self::generateDueDate(),
                ],
                ['status'],
            ],
            'Invalid due date' => [
                [
                    'title' => self::$faker->sentence,
                    'description' => self::generateDescription(),
                    'status' => self::generateStatus(),
                    'due_date' => self::generateInvalidDueDate(),
                ],
                ['due_date'],
            ],
            'Missing due date' => [
                [
                    'title' => self::$faker->sentence,
                    'description' => self::generateDescription(),
                    'status' => self::generateStatus(),
                    'due_date' => null,
                ],
                ['due_date'],
            ],
            'Missing title and invalid due date' => [
                [
                    'description' => self::generateDescription(),
                    'due_date' => self::generateInvalidDueDate(),
                    'status' => self::generateStatus(),
                ],
                ['title', 'due_date'],
            ],
            'Missing title and invalid status' => [
                [
                    'description' => self::generateDescription(),
                    'due_date' => self::generateDueDate(),
                    'status' => 'ready',
                ],
                ['title', 'status'],
            ],
            'Missing due date and invalid status' => [
                [
                    'title' => self::$faker->sentence,
                    'description' => self::generateDescription(),
                    'status' => 'ready',
                ],
                ['due_date', 'status'],
            ],
            'Missing due date and title, invalid status' => [
                [
                    'description' => self::generateDescription(),
                    'status' => 'ready',
                ],
                ['title', 'status', 'due_date']
            ]
        ];
    }

    public static function taskUpdateDataProvider(): array
    {
        return [
            'Title only' => [
                [
                    'title' => self::$faker->sentence,
                ],
                []
            ],
            'Description only' => [
                [
                    'description' => self::generateDescription(),
                ],
                []
            ],
            'Status only' => [
                [
                    'status' => self::generateStatus(),
                ],
                []
            ],
            'Due date only' => [
                [
                    'due_date' => self::generateDueDate(),
                ],
                []
            ],
            'All fields' => [
                [
                    'title' => self::$faker->sentence,
                    'description' => self::generateDescription(),
                    'status' => self::generateStatus(),
                    'due_date' => self::generateDueDate(),
                ],
                []
            ],
            'Invalid title' => [
                [
                    'title' => null,
                ],
                ['title']
            ],
            'Invalid status' => [
                [
                    'status' => 'ready',
                ],
                ['status']
            ],
            'Invalid due date' => [
                [
                    'due_date' => self::generateInvalidDueDate(),
                ],
                ['due_date']
            ],
            'Invalid title, status and due date' => [
                [
                    'title' => null,
                    'status' => 'ready',
                    'due_date' => self::generateInvalidDueDate(),
                ],
                ['title', 'status', 'due_date']
            ]
        ];
    }

    private function assertUpdateCompleted(array $taskData, array $updateData, array $originalTaskData): void
    {
        foreach ($updateData as $field => $value) {
            $this->assertEquals($taskData[$field], $value);
        }

        foreach ($originalTaskData as $field => $value) {
            if (array_key_exists($field, $updateData)) {
                continue;
            }

            if ('updated_at' === $field) {
                continue;
            }

            $this->assertEquals($value, $taskData[$field]);
        }
    }

    private function assertTaskStructure(array $task): void
    {
        $this->assertArrayHasKey('id', $task);
        $this->assertArrayHasKey('title', $task);
        $this->assertArrayHasKey('description', $task);
        $this->assertArrayHasKey('status', $task);
        $this->assertArrayHasKey('due_date', $task);
        $this->assertArrayHasKey('created_at', $task);
        $this->assertArrayHasKey('updated_at', $task);

        $this->assertIsInt($task['id']);
        $this->assertIsString($task['title']);
        $this->assertContains($task['status'], self::TASK_STATUSES);
    }

    private function assertValidationErrorsMatch(array $response, array $expectedErrorFields): void
    {
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('errors', $response);

        $this->assertIsArray($response['errors']);

        $validationErrors = array_keys($response['errors']);

        foreach ($expectedErrorFields as $expectedErrorField) {
            $this->assertContains($expectedErrorField, $validationErrors);
        }

        foreach ($validationErrors as $validationError) {
            $this->assertContains($validationError, $expectedErrorFields);
        }
    }

    private static function generateDescription(): ?string
    {
        return (random_int(0, 1)) ? self::$faker->sentences(asText: true) : null;
    }

    private static function generateDueDate(): string
    {
        return (new DateTime())
            ->add(new DateInterval(sprintf('P%dD', random_int(2, 7))))
            ->format('Y-m-d');
    }

    private static function generateInvalidDueDate(): string
    {
        return (new DateTime())
            ->sub(new DateInterval(sprintf('P%dD', random_int(2, 7))))
            ->format('Y-m-d');
    }

    private static function generateStatus(): string
    {
        return self::$faker->randomElement(self::TASK_STATUSES);
    }

    private function getRandomTask(): array
    {
        $this->sendGet('/api/tasks');

        $response = $this->getResponseArray();

        $this->assertArrayHasKey('data', $response);

        $task = self::$faker->randomElement($response['data']);

        $this->assertTaskStructure($task);

        return $task;
    }
}
