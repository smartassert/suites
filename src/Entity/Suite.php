<?php

namespace App\Entity;

use App\Model\EntityId;
use App\Repository\SuiteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuiteRepository::class)]
class Suite implements \JsonSerializable
{
    public const ID_LENGTH = 32;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: self::ID_LENGTH, unique: true)]
    protected string $id;

    #[ORM\Column(type: 'string', length: self::ID_LENGTH)]
    private string $userId;

    #[ORM\Column(type: 'string', length: self::ID_LENGTH)]
    private string $sourceId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $label;

    /**
     * @var null|array<int, string>
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $tests;

    /**
     * @param null|string[] $tests
     */
    public function __construct(string $userId, string $sourceId, string $label, ?array $tests = null)
    {
        $this->id = EntityId::create();
        $this->userId = $userId;
        $this->sourceId = $sourceId;
        $this->label = $label;
        $this->tests = $tests;
    }

    public function setSourceId(string $sourceId): self
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param null|string[] $tests
     */
    public function setTests(?array $tests): self
    {
        $this->tests = $tests;

        return $this;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return array{
     *     id: string,
     *     source_id: string,
     *     label: string,
     *     tests?: array<int, string>
     * }
     */
    public function jsonSerialize(): array
    {
        $data = [
            'id' => $this->id,
            'source_id' => $this->sourceId,
            'label' => $this->label,
        ];

        if (is_array($this->tests)) {
            $data['tests'] = $this->tests;
        }

        return $data;
    }
}
