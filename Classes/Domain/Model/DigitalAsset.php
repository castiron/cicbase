<?php
namespace CIC\Cicbase\Domain\Model;

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Nils Blattner <nb@cabag.ch>, cab services ag
*  			
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This model is an object representation of the DAM table.
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DigitalAsset extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
	
	/**
	 * mediaType
	 * @var int
	 */
	protected $mediaType;
	
	/**
	 * title
	 * @var string
	 */
	protected $title;
	
	/**
	 * category
	 * @var int
	 */
	protected $category;
	
	/**
	 * indexType
	 * @var string
	 */
	protected $indexType;
	
	/**
	 * fileMimeType
	 * @var string
	 */
	protected $fileMimeType;
	
	/**
	 * fileMimeSubtype
	 * @var string
	 */
	protected $fileMimeSubtype;
	
	/**
	 * fileType
	 * @var string
	 */
	protected $fileType;
	
	/**
	 * fileTypeVersion
	 * @var string
	 */
	protected $fileTypeVersion;
	
	/**
	 * fileName
	 * @var string
	 */
	protected $fileName;
	
	/**
	 * filePath
	 * @var string
	 */
	protected $filePath;
	
	/**
	 * fileSize
	 * @var int
	 */
	protected $fileSize;
	
	/**
	 * fileMtime
	 * @var DateTime
	 */
	protected $fileMtime;
	
	/**
	 * fileInode
	 * @var int
	 */
	protected $fileInode;
	
	/**
	 * fileCtime
	 * @var DateTime
	 */
	protected $fileCtime;
	
	/**
	 * fileHash
	 * @var string
	 */
	protected $fileHash;
	
	/**
	 * fileStatus
	 * @var int
	 */
	protected $fileStatus;
	
	/**
	 * fileOrigLocation
	 * @var string
	 */
	protected $fileOrigLocation;
	
	/**
	 * fileOrigLocDesc
	 * @var string
	 */
	protected $fileOrigLocDesc;
	
	/**
	 * fileCreator
	 * @var string
	 */
	protected $fileCreator;
	
	/**
	 * fileDlName
	 * @var string
	 */
	protected $fileDlName;
	
	/**
	 * fileUsage
	 * @var int
	 */
	protected $fileUsage;
	
	/**
	 * meta
	 * @var string
	 */
	protected $meta;
	
	/**
	 * ident
	 * @var string
	 */
	protected $ident;
	
	/**
	 * creator
	 * @var string
	 */
	protected $creator;
	
	/**
	 * keywords
	 * @var string
	 */
	protected $keywords;
	
	/**
	 * description
	 * @var string
	 */
	protected $description;
	
	/**
	 * string
	 * @var string
	 */
	protected $altText;
	
	/**
	 * caption
	 * @var string
	 */
	protected $caption;
	
	/**
	 * abstract
	 * @var string
	 */
	protected $abstract;
	
	/**
	 * searchContent
	 * @var string
	 */
	protected $searchContent;
	
	/**
	 * language
	 * @var string
	 */
	protected $language;
	
	/**
	 * pages
	 * @var int
	 */
	protected $pages;
	
	/**
	 * publisher
	 * @var string
	 */
	protected $publisher;
	
	/**
	 * copyright
	 * @var string
	 */
	protected $copyright;
	
	/**
	 * instructions
	 * @var string
	 */
	protected $instructions;
	
	/**
	 * dateCr
	 * @var DateTime
	 */
	protected $dateCr;
	
	/**
	 * dateMod
	 * @var DateTime
	 */
	protected $dateMod;
	
	/**
	 * locDesc
	 * @var string
	 */
	protected $locDesc;
	
	/**
	 * locCountry
	 * @var string
	 */
	protected $locCountry;
	
	/**
	 * locCity
	 * @var string
	 */
	protected $locCity;
	
	/**
	 * hres
	 * @var int
	 */
	protected $hres;
	
	/**
	 * vres
	 * @var int
	 */
	protected $vres;
	
	/**
	 * hpixels
	 * @var int
	 */
	protected $hpixels;
	
	/**
	 * vpixels
	 * @var int
	 */
	protected $vpixels;
	
	/**
	 * colorSpace
	 * @var string
	 */
	protected $colorSpace;
	
	/**
	 * width
	 * @var float
	 */
	protected $width;
	
	/**
	 * height
	 * @var float
	 */
	protected $height;
	
	/**
	 * heightUnit
	 * @var string
	 */
	protected $heightUnit;
	
		/**
	 * Setter for mediaType
	 *
	 * @param int
	 * @return void
	 */
	public function setMediaType($mediaType) {
		$this->mediaType = $mediaType;
	}
	
	/**
	 * Getter for mediaType
	 *
	 * @return int
	 */
	public function getMediaType() {
		return $this->mediaType;
	}
	
	/**
	 * Setter for title
	 *
	 * @param string
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * Getter for title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Setter for category
	 *
	 * @param int
	 * @return void
	 */
	public function setCategory($category) {
		$this->category = $category;
	}
	
	/**
	 * Getter for category
	 *
	 * @return int
	 */
	public function getCategory() {
		return $this->category;
	}
	
	/**
	 * Setter for indexType
	 *
	 * @param string
	 * @return void
	 */
	public function setIndexType($indexType) {
		$this->indexType = $indexType;
	}
	
	/**
	 * Getter for indexType
	 *
	 * @return string
	 */
	public function getIndexType() {
		return $this->indexType;
	}
	
	/**
	 * Setter for fileMimeType
	 *
	 * @param string
	 * @return void
	 */
	public function setFileMimeType($fileMimeType) {
		$this->fileMimeType = $fileMimeType;
	}
	
	/**
	 * Getter for fileMimeType
	 *
	 * @return string
	 */
	public function getFileMimeType() {
		return $this->fileMimeType;
	}
	
	/**
	 * Setter for fileMimeSubtype
	 *
	 * @param string
	 * @return void
	 */
	public function setFileMimeSubtype($fileMimeSubtype) {
		$this->fileMimeSubtype = $fileMimeSubtype;
	}
	
	/**
	 * Getter for fileMimeSubtype
	 *
	 * @return string
	 */
	public function getFileMimeSubtype() {
		return $this->fileMimeSubtype;
	}
	
	/**
	 * Setter for fileType
	 *
	 * @param string
	 * @return void
	 */
	public function setFileType($fileType) {
		$this->fileType = $fileType;
	}
	
	/**
	 * Getter for fileType
	 *
	 * @return string
	 */
	public function getFileType() {
		return $this->fileType;
	}
	
	/**
	 * Setter for fileTypeVersion
	 *
	 * @param string
	 * @return void
	 */
	public function setFileTypeVersion($fileTypeVersion) {
		$this->fileTypeVersion = $fileTypeVersion;
	}
	
	/**
	 * Getter for fileTypeVersion
	 *
	 * @return string
	 */
	public function getFileTypeVersion() {
		return $this->fileTypeVersion;
	}
	
	/**
	 * Setter for fileName
	 *
	 * @param string
	 * @return void
	 */
	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}
	
	/**
	 * Getter for fileName
	 *
	 * @return string
	 */
	public function getFileName() {
		return $this->fileName;
	}
	
	/**
	 * Setter for filePath
	 *
	 * @param string
	 * @return void
	 */
	public function setFilePath($filePath) {
		$this->filePath = $filePath;
	}
	
	/**
	 * Getter for filePath
	 *
	 * @return string
	 */
	public function getFilePath() {
		return $this->filePath;
	}
	
	/**
	 * Setter for fileSize
	 *
	 * @param int
	 * @return void
	 */
	public function setFileSize($fileSize) {
		$this->fileSize = $fileSize;
	}
	
	/**
	 * Getter for fileSize
	 *
	 * @return int
	 */
	public function getFileSize() {
		return $this->fileSize;
	}
	
	/**
	 * Setter for fileMtime
	 *
	 * @param DateTime
	 * @return void
	 */
	public function setFileMtime($fileMtime) {
		$this->fileMtime = $fileMtime;
	}
	
	/**
	 * Getter for fileMtime
	 *
	 * @return DateTime
	 */
	public function getFileMtime() {
		return $this->fileMtime;
	}
	
	/**
	 * Setter for fileInode
	 *
	 * @param int
	 * @return void
	 */
	public function setFileInode($fileInode) {
		$this->fileInode = $fileInode;
	}
	
	/**
	 * Getter for fileInode
	 *
	 * @return int
	 */
	public function getFileInode() {
		return $this->fileInode;
	}
	
	/**
	 * Setter for fileCtime
	 *
	 * @param DateTime
	 * @return void
	 */
	public function setFileCtime($fileCtime) {
		$this->fileCtime = $fileCtime;
	}
	
	/**
	 * Getter for fileCtime
	 *
	 * @return DateTime
	 */
	public function getFileCtime() {
		return $this->fileCtime;
	}
	
	/**
	 * Setter for fileHash
	 *
	 * @param string
	 * @return void
	 */
	public function setFileHash($fileHash) {
		$this->fileHash = $fileHash;
	}
	
	/**
	 * Getter for fileHash
	 *
	 * @return string
	 */
	public function getFileHash() {
		return $this->fileHash;
	}
	
	/**
	 * Setter for fileStatus
	 *
	 * @param int
	 * @return void
	 */
	public function setFileStatus($fileStatus) {
		$this->fileStatus = $fileStatus;
	}
	
	/**
	 * Getter for fileStatus
	 *
	 * @return int
	 */
	public function getFileStatus() {
		return $this->fileStatus;
	}
	
	/**
	 * Setter for fileOrigLocation
	 *
	 * @param string
	 * @return void
	 */
	public function setFileOrigLocation($fileOrigLocation) {
		$this->fileOrigLocation = $fileOrigLocation;
	}
	
	/**
	 * Getter for fileOrigLocation
	 *
	 * @return string
	 */
	public function getFileOrigLocation() {
		return $this->fileOrigLocation;
	}
	
	/**
	 * Setter for fileOrigLocDesc
	 *
	 * @param string
	 * @return void
	 */
	public function setFileOrigLocDesc($fileOrigLocDesc) {
		$this->fileOrigLocDesc = $fileOrigLocDesc;
	}
	
	/**
	 * Getter for fileOrigLocDesc
	 *
	 * @return string
	 */
	public function getFileOrigLocDesc() {
		return $this->fileOrigLocDesc;
	}
	
	/**
	 * Setter for fileCreator
	 *
	 * @param string
	 * @return void
	 */
	public function setFileCreator($fileCreator) {
		$this->fileCreator = $fileCreator;
	}
	
	/**
	 * Getter for fileCreator
	 *
	 * @return string
	 */
	public function getFileCreator() {
		return $this->fileCreator;
	}
	
	/**
	 * Setter for fileDlName
	 *
	 * @param string
	 * @return void
	 */
	public function setFileDlName($fileDlName) {
		$this->fileDlName = $fileDlName;
	}
	
	/**
	 * Getter for fileDlName
	 *
	 * @return string
	 */
	public function getFileDlName() {
		return $this->fileDlName;
	}
	
	/**
	 * Setter for fileUsage
	 *
	 * @param int
	 * @return void
	 */
	public function setFileUsage($fileUsage) {
		$this->fileUsage = $fileUsage;
	}
	
	/**
	 * Getter for fileUsage
	 *
	 * @return int
	 */
	public function getFileUsage() {
		return $this->fileUsage;
	}
	
	/**
	 * Setter for meta
	 *
	 * @param string
	 * @return void
	 */
	public function setMeta($meta) {
		$this->meta = $meta;
	}
	
	/**
	 * Getter for meta
	 *
	 * @return string
	 */
	public function getMeta() {
		return $this->meta;
	}
	
	/**
	 * Setter for ident
	 *
	 * @param string
	 * @return void
	 */
	public function setIdent($ident) {
		$this->ident = $ident;
	}
	
	/**
	 * Getter for ident
	 *
	 * @return string
	 */
	public function getIdent() {
		return $this->ident;
	}
	
	/**
	 * Setter for creator
	 *
	 * @param string
	 * @return void
	 */
	public function setCreator($creator) {
		$this->creator = $creator;
	}
	
	/**
	 * Getter for creator
	 *
	 * @return string
	 */
	public function getCreator() {
		return $this->creator;
	}
	
	/**
	 * Setter for keywords
	 *
	 * @param string
	 * @return void
	 */
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}
	
	/**
	 * Getter for keywords
	 *
	 * @return string
	 */
	public function getKeywords() {
		return $this->keywords;
	}
	
	/**
	 * Setter for description
	 *
	 * @param string
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}
	
	/**
	 * Getter for description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Setter for altText
	 *
	 * @param string
	 * @return void
	 */
	public function setAltText($altText) {
		$this->altText = $altText;
	}
	
	/**
	 * Getter for altText
	 *
	 * @return string
	 */
	public function getAltText() {
		return $this->altText;
	}
	
	/**
	 * Setter for caption
	 *
	 * @param string
	 * @return void
	 */
	public function setCaption($caption) {
		$this->caption = $caption;
	}
	
	/**
	 * Getter for caption
	 *
	 * @return string
	 */
	public function getCaption() {
		return $this->caption;
	}
	
	/**
	 * Setter for abstract
	 *
	 * @param string
	 * @return void
	 */
	public function setAbstract($abstract) {
		$this->abstract = $abstract;
	}
	
	/**
	 * Getter for abstract
	 *
	 * @return string
	 */
	public function getAbstract() {
		return $this->abstract;
	}
	
	/**
	 * Setter for searchContent
	 *
	 * @param string
	 * @return void
	 */
	public function setSearchContent($searchContent) {
		$this->searchContent = $searchContent;
	}
	
	/**
	 * Getter for searchContent
	 *
	 * @return string
	 */
	public function getSearchContent() {
		return $this->searchContent;
	}
	
	/**
	 * Setter for language
	 *
	 * @param string
	 * @return void
	 */
	public function setLanguage($language) {
		$this->language = $language;
	}
	
	/**
	 * Getter for language
	 *
	 * @return string
	 */
	public function getLanguage() {
		return $this->language;
	}
	
	/**
	 * Setter for pages
	 *
	 * @param int
	 * @return void
	 */
	public function setPages($pages) {
		$this->pages = $pages;
	}
	
	/**
	 * Getter for pages
	 *
	 * @return int
	 */
	public function getPages() {
		return $this->pages;
	}
	
	/**
	 * Setter for publisher
	 *
	 * @param string
	 * @return void
	 */
	public function setPublisher($publisher) {
		$this->publisher = $publisher;
	}
	
	/**
	 * Getter for publisher
	 *
	 * @return string
	 */
	public function getPublisher() {
		return $this->publisher;
	}
	
	/**
	 * Setter for copyright
	 *
	 * @param string
	 * @return void
	 */
	public function setCopyright($copyright) {
		$this->copyright = $copyright;
	}
	
	/**
	 * Getter for copyright
	 *
	 * @return string
	 */
	public function getCopyright() {
		return $this->copyright;
	}
	
	/**
	 * Setter for instructions
	 *
	 * @param string
	 * @return void
	 */
	public function setInstructions($instructions) {
		$this->instructions = $instructions;
	}
	
	/**
	 * Getter for instructions
	 *
	 * @return string
	 */
	public function getInstructions() {
		return $this->instructions;
	}
	
	/**
	 * Setter for dateCr
	 *
	 * @param DateTime
	 * @return void
	 */
	public function setDateCr($dateCr) {
		$this->dateCr = $dateCr;
	}
	
	/**
	 * Getter for dateCr
	 *
	 * @return DateTime
	 */
	public function getDateCr() {
		return $this->dateCr;
	}
	
	/**
	 * Setter for dateMod
	 *
	 * @param DateTime
	 * @return void
	 */
	public function setDateMod($dateMod) {
		$this->dateMod = $dateMod;
	}
	
	/**
	 * Getter for dateMod
	 *
	 * @return DateTime
	 */
	public function getDateMod() {
		return $this->dateMod;
	}
	
	/**
	 * Setter for locDesc
	 *
	 * @param string
	 * @return void
	 */
	public function setLocDesc($locDesc) {
		$this->locDesc = $locDesc;
	}
	
	/**
	 * Getter for locDesc
	 *
	 * @return string
	 */
	public function getLocDesc() {
		return $this->locDesc;
	}
	
	/**
	 * Setter for locCountry
	 *
	 * @param string
	 * @return void
	 */
	public function setLocCountry($locCountry) {
		$this->locCountry = $locCountry;
	}
	
	/**
	 * Getter for locCountry
	 *
	 * @return string
	 */
	public function getLocCountry() {
		return $this->locCountry;
	}
	
	/**
	 * Setter for locCity
	 *
	 * @param string
	 * @return void
	 */
	public function setLocCity($locCity) {
		$this->locCity = $locCity;
	}
	
	/**
	 * Getter for locCity
	 *
	 * @return string
	 */
	public function getLocCity() {
		return $this->locCity;
	}
	
	/**
	 * Setter for hres
	 *
	 * @param int
	 * @return void
	 */
	public function setHres($hres) {
		$this->hres = $hres;
	}
	
	/**
	 * Getter for hres
	 *
	 * @return int
	 */
	public function getHres() {
		return $this->hres;
	}
	
	/**
	 * Setter for vres
	 *
	 * @param int
	 * @return void
	 */
	public function setVres($vres) {
		$this->vres = $vres;
	}
	
	/**
	 * Getter for vres
	 *
	 * @return int
	 */
	public function getVres() {
		return $this->vres;
	}
	
	/**
	 * Setter for hpixels
	 *
	 * @param int
	 * @return void
	 */
	public function setHpixels($hpixels) {
		$this->hpixels = $hpixels;
	}
	
	/**
	 * Getter for hpixels
	 *
	 * @return int
	 */
	public function getHpixels() {
		return $this->hpixels;
	}
	
	/**
	 * Setter for vpixels
	 *
	 * @param int
	 * @return void
	 */
	public function setVpixels($vpixels) {
		$this->vpixels = $vpixels;
	}
	
	/**
	 * Getter for vpixels
	 *
	 * @return int
	 */
	public function getVpixels() {
		return $this->vpixels;
	}
	
	/**
	 * Setter for colorSpace
	 *
	 * @param string
	 * @return void
	 */
	public function setColorSpace($colorSpace) {
		$this->colorSpace = $colorSpace;
	}
	
	/**
	 * Getter for colorSpace
	 *
	 * @return string
	 */
	public function getColorSpace() {
		return $this->colorSpace;
	}
	
	/**
	 * Setter for width
	 *
	 * @param float
	 * @return void
	 */
	public function setWidth($width) {
		$this->width = $width;
	}
	
	/**
	 * Getter for width
	 *
	 * @return float
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * Setter for height
	 *
	 * @param float
	 * @return void
	 */
	public function setHeight($height) {
		$this->height = $height;
	}
	
	/**
	 * Getter for height
	 *
	 * @return float
	 */
	public function getHeight() {
		return $this->height;
	}
	
	/**
	 * Setter for heightUnit
	 *
	 * @param string
	 * @return void
	 */
	public function setHeightUnit($heightUnit) {
		$this->heightUnit = $heightUnit;
	}
	
	/**
	 * Getter for heightUnit
	 *
	 * @return string
	 */
	public function getHeightUnit() {
		return $this->heightUnit;
	}
	
	/**
	 * Returns the full (relative) path.
	 *
	 * @return string
	 */
	public function getFullPath() {
		return $this->filePath . $this->fileName;
	}
}

?>
