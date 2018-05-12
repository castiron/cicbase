<?php namespace CIC\Cicbase\ViewHelpers;

use CIC\Cicbase\Utility\File;

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

    /**
     * Resizes a given image (if required) and renders the respective img tag
     *
     * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
     * @param string $src a path to a file, a combined FAL identifier or an uid (integer). If $treatIdAsReference is set, the integer is considered the uid of the sys_file_reference record. If you already got a FAL object, consider using the $image parameter instead
     * @param string $width width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
     * @param string $height height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
     * @param integer $minWidth minimum width of the image
     * @param integer $minHeight minimum height of the image
     * @param integer $maxWidth maximum width of the image
     * @param integer $maxHeight maximum height of the image
     * @param boolean $treatIdAsReference given src argument is a sys_file_reference record
     * @param FileInterface|AbstractFileFolder $image a FAL object
     * @param string $additionalParameters raw imagemagick params to pass to the object
     *
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     * @return string Rendered tag
     */
    public function render($src = NULL, $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL, $treatIdAsReference = FALSE, $image = NULL, $additionalParameters = '') {
        if (is_null($src) && is_null($image) || !is_null($src) && !is_null($image)) {
            throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('You must either specify a string src or a File object.', 1382284106);
        }

        try {
            if ($image = $this->imageService->getImage($src, $image, $treatIdAsReference)) {
                if (!File::isProcessableFile($image)) {
                    return '';
                }
                $processingInstructions = array(
                    'width' => $width,
                    'height' => $height,
                    'minWidth' => $minWidth,
                    'minHeight' => $minHeight,
                    'maxWidth' => $maxWidth,
                    'maxHeight' => $maxHeight,
                    'additionalParameters' => $additionalParameters ? $additionalParameters : $this->getDefaultAdditionalImagemagickParams($image),
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

        } catch (ResourceDoesNotExistException $e) {
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
