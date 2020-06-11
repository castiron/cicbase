<?php

namespace CIC\Cicbase\ViewHelpers\File;

/***************************************************************
 *  Copyright notice
 *  (c) 2012 Peter Soots <peter
 * @castironcoding.com>, Cast Iron Coding
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class LinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @var bool
	 */
	protected $escapeOutput = false;

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('file', 'object', 'File reference', true);
		$this->registerArgument('class', 'string', 'HTML class', false, null);
		$this->registerArgument('linkText', 'string', 'Link text', false, null);
	}

	/**
	 * @return string
	 */
	public function render() {
		extract($this->arguments);

		if ($file instanceof \TYPO3\CMS\Extbase\Domain\Model\FileReference) {
			$uri = $file->getOriginalResource()->getPublicUrl();
		} elseif ($file) {
			$uri = $file->getPathAndFileName();
		} else {
			return '';
		}

		if($linkText) {
			if($linkText == '__renderChildren__') {
				$text = $this->renderChildren();
			} else {
				$text = $linkText;
			}
		} elseif($file->getTitle()) {
			$text = $file->getTitle();
		} else {
			$text = $file->getOriginalFilename();
		}

		if($class)
			$classAttr = 'class="'.$class.'"';
		else
			$classAttr = '';
		return '<a target="_blank" href="'.$uri.'" '.$classAttr.'>'.$text.'</a>';
	}
}
