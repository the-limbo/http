<?php declare(strict_types=1);


namespace Limbo\Http\Message;

use RuntimeException;
use Psr\Http\Message\StreamInterface;
use Limbo\Http\Factory\StreamFactory;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    /**
     * The file stream
     *
     * @var StreamInterface|null
     */
    protected ?StreamInterface $stream;

    /**
     * The file size
     *
     * @var int|null
     */
    protected ?int $size;

    /**
     * The file error
     *
     * @var int
     */
    protected int $error;

    /**
     * The file name
     *
     * @var string|null
     */
    protected ?string $clientFilename;

    /**
     * The file type
     *
     * @var string|null
     */
    protected ?string $clientMediaType;

    /**
     * Constructor of the class
     * @param StreamInterface $stream
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    public function __construct(
        StreamInterface $stream,
        int $size = null,
        int $error = UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ) {
        $this->stream = $stream;
        $this->size = $size ?? $stream->getSize();
        $this->error = $error;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * @inheritDoc
     */
    public function getStream(): ?StreamInterface
    {
        if (!($this->stream instanceof StreamInterface)) {
            throw new RuntimeException('The uploaded file already moved');
        }

        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    public function moveTo($targetPath)
    {
        if (!($this->stream instanceof StreamInterface)) {
            throw new RuntimeException('The uploaded file already moved');
        }

        if (!(UPLOAD_ERR_OK === $this->error)) {
            throw new RuntimeException('The uploaded file cannot be moved due to an error');
        }

        $folder = dirname($targetPath);
        if (!is_dir($folder)) {
            throw new RuntimeException(
                sprintf('The uploaded file cannot be moved. The directory "%s" does not exist', $folder)
            );
        }

        if (!is_writeable($folder)) {
            throw new RuntimeException(
                sprintf('The uploaded file cannot be moved. The directory "%s" is not writeable', $folder)
            );
        }

        $target = (new StreamFactory)->createStreamFromFile($targetPath, 'wb');

        $this->stream->rewind();
        while (!$this->stream->eof()) {
            $target->write($this->stream->read(4096));
        }

        $this->stream->close();
        $this->stream = null;

        $target->close();
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @inheritDoc
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * @inheritDoc
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }
}
