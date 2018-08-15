<?php namespace CIC\Cicbase\ViewHelpers;

use CIC\Cicbase\Utility\File;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;

/**
 * Class ImageViewHelper
 * @package CIC\Cicbase\ViewHelpers
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper {
    /**
     * @param \TYPO3\CMS\Core\Resource\FileInterface $image
     * @return string
     */
    protected function getDefaultAdditionalImagemagickParams(\TYPO3\CMS\Core\Resource\FileInterface $image) {
        if ($image->getExtension() === 'pdf') {
            return $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_pdf_params'];
        }
        return '';
    }

    /**
     * @param \TYPO3\CMS\Core\Resource\FileInterface $image
     * @return bool
     */
    protected function isWebEmbeddable(\TYPO3\CMS\Core\Resource\FileInterface $image) {
        return $image->getExtension() !== 'pdf';
    }

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('additionalParameters', 'string', 'Raw imagemagick params to pass to the object');
    }

    /**
     * Resizes a given image (if required) and renders the respective img tag
     *
     * @return string
     */
    public function render() {
        if (is_null($this->arguments['src']) && is_null($this->arguments['image']) || !is_null($this->arguments['src']) && !is_null($this->arguments['image'])) {
            throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('You must either specify a string src or a File object.', 1382284106);
        }

        try {
            if ($image = $this->imageService->getImage($this->arguments['src'], $this->arguments['image'], $this->arguments['treatIdAsReference'])) {
                if (!File::isProcessableFile($image)) {
                    return '';
                }
                $processingInstructions = array(
                    'width' => $this->arguments['width'],
                    'height' => $this->arguments['height'],
                    'minWidth' => $this->arguments['minWidth'],
                    'minHeight' => $this->arguments['minHeight'],
                    'maxWidth' => $this->arguments['maxWidth'],
                    'maxHeight' => $this->arguments['maxHeight'],
                    'additionalParameters' => $this->arguments['additionalParameters'] ? $this->arguments['additionalParameters'] : $this->getDefaultAdditionalImagemagickParams($image),
                );

                $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);
                $imageUri = $this->imageService->getImageUri($processedImage);

                /**
                 * Sometimes an image just can't be generated from certain file types in the current gm/gs/ImageMagick setup.
                 * We don't want a .pdf URL in an img src attribute, for example, so we just fully bail here in that
                 * case...
                 */
                if (
                    !$this->isWebEmbeddable($image)
                    && ltrim($imageUri, '/') == ltrim($image->getPublicUrl(), '/')
                ) {
                    return '';
                }

                $this->tag->addAttribute('src', $imageUri);
                $this->tag->addAttribute('width', $processedImage->getProperty('width'));
                $this->tag->addAttribute('height', $processedImage->getProperty('height'));

                $alt = $image->getProperty('alternative');
                $title = $image->getProperty('title');

                // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
                if (empty($this->arguments['alt'])) {
                    $this->tag->addAttribute('alt', $alt);
                }
                if (empty($this->arguments['title']) && $title) {
                    $this->tag->addAttribute('title', $title);
                }
            }

        } catch (FolderDoesNotExistException $e) {
            // thrown if file does not exist
        } catch (\UnexpectedValueException $e) {
            // thrown if a file has been replaced with a folder
        } catch (\RuntimeException $e) {
            // RuntimeException thrown if a file is outside of a storage
        } catch (\InvalidArgumentException $e) {
            // thrown if file storage does not exist
        }

        return $this->tag->render();
    }
}
