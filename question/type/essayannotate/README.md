## About the plugin
The essay annotate question type is an extension to [essay question type](https://docs.moodle.org/404/en/Essay_question_type) that allows the teacher to annotate the files uploaded as attachments by the student in an answer. The file types supported include PDF and those with mime type image (such as `.png` and `.jpeg`) and mime type text (such as `.py`, `.c`, `.cpp`, and `.txt`). The plugin requires the permission to execute external binary files on the Moodle server. The user documentation can be found in the [Wiki section](https://github.com/vmsreeram/moodle-qtype_essayannotate/wiki).

Technical report and demo video can be found [here](https://github.com/vmsreeram/moodle-qtype_essayannotate-documentation).


## Dependencies
- [ImageMagick](https://imagemagick.org/)
- [Ghostscript](https://www.ghostscript.com/)

## Supported versions
Tested on Moodle versions 4.0 to 4.4

## Installation steps
1. Installing the plugin
      <details>
      <summary>Using GUI</summary>
    
      - Download the repository as a ZIP file by navigating to `Code` > `Local` > `Download ZIP` in the homepage of the repository, or using this [link](https://github.com/vmsreeram/essayannotate/archive/refs/heads/main.zip).
      - Login to Moodle as Admin
      - Navigate to `Site administration` > `Plugins` > `Install plugins`
      - Upload the ZIP file to `ZIP package` section.
      - Expand `Show more...`
      - Choose `Plugin type` as `Question type (qtype)`.
      - Rename the root directory to `essayannotate`.
      - Click on `Install plugin from ZIP file` and follow the prompted steps.
      </details>
      
      <details>
      <summary>Manually</summary>
    
      - Get the contents of the repo by either
        - Downloading the repository as a ZIP file by navigating to `Code` > `Local` > `Download ZIP` in the homepage of the repository, or using this [link](https://github.com/vmsreeram/essayannotate/archive/refs/heads/main.zip). Then unzip the repo, and rename the folder name to `essayannotate`.
        - Cloning this repo. Then rename the folder name to `essayannotate`.
      - Move this folder into the  `question/type` folder on your Moodle site, to create a new folder at `question/type/essayannotate`.
      - Login to Moodle as Admin 
      - Follow the prompted steps to install the plugin. If install/upgrade does not begin automatically, you can initiate it manually by navigating to the following link: `Administration` > `Site administration` > `Notifications`
      </details>

2. Installing the dependencies (if required)
   - Install ImageMagick from [here](https://imagemagick.org/script/download.php).
   - Install Ghostscript from [here](https://ghostscript.com/releases/gsdnld.html).

2. Configuring the settings
   - Once installation is successful, you will be prompted to enter ImageMagick Path and Ghostscript Path. You can get these by running `which convert` and `which gs` respectively.
   - Replace the following line in `/etc/ImageMagick-xx/policy.xml` (`xx` is the version number).

      ```xml
       <policy domain="coder" rights="none" pattern="PDF" />
      ```
      
       with
     
      ```xml
       <policy domain="coder" rights="read|write" pattern="PDF" />
      ```
     To check ImageMagick is working, convert a png file to a pdf, in terminal run `convert file.png file.pdf`.
    - Create a directory `essayPDF` with write permission to the user running the Moodle server in the `temp` dir of Moodle data.

</details>

## The Team
This work has been completed as part of three Undergraduate Major Projects at [IIT Palakkad](https://www.iitpkd.ac.in/) over the years 2021-22, 2022-23, and 2023-24.

| Project Mentor     |  Email ID              |
|--------------------|------------------------|
| [Dr. Jasine Babu](https://www.iitpkd.ac.in/people/jasine)    | jasine@iitpkd.ac.in    |

<table>
  <tr>
    <th>Developers</th>
    <th>Email ID</th>
    <th>Year</th>
    <th>Repo</th>
  </tr>
  <tr>
    <td><a href="https://www.linkedin.com/in/nideesh-n-22497a128/">Nideesh N</a></td>
    <td>nideeshnagarajan@gmail.com</td>
    <td rowspan=2>2023-24</td>
    <td rowspan=2><a href="https://github.com/vmsreeram/moodle-qtype_essayannotate">https://github.com/vmsreeram/moodle-qtype_essayannotate</a></td>
  </tr>
  <tr>
    <td><a href="https://www.linkedin.com/in/vm-sreeram/">VM Sreeram</a></td>
    <td>vm.sreeram02@gmail.com</td>
  </tr>
  <tr>
    <td><a href="https://www.linkedin.com/in/asha-jose-5b8031218/">Asha Jose</a></td>
    <td>ashaj0308@gmail.com</td>
    <td rowspan=2>2022-23</td>
    <td rowspan=2><a href="https://github.com/Parvathy-S-Kumar/Moodle_Quiz_PDF_Annotator">github.com/Parvathy-S-Kumar/Moodle_Quiz_PDF_Annotator</a></td>
  </tr>
  <tr>
    <td><a href="https://www.linkedin.com/in/parvathy-s-kumar-288aa01b2/">Parvathy S Kumar</a></td>
    <td>parvathyskumar187@gmail.com</td>
  </tr>
  <tr>
    <td><a href="https://www.linkedin.com/in/vishal-rao-910809190/">Vishal Rao</a></td>
    <td>raovishal487@gmail.com</td>
    <td rowspan=2>2021-22</td>
    <td rowspan=2><a href="https://github.com/TausifIqbal/moodle_quiz_annotator">github.com/TausifIqbal/moodle_quiz_annotator</a></td>
  </tr>
  <tr>
    <td><a href="https://www.linkedin.com/in/tausif-iqbal-ti/">Tausif Iqbal</a></td>
    <td>tausifiqbalti21@gmail.com</td>
  </tr>
</table>

Special thanks to Mr. Sumesh K S (Technical Supdt.) & Mr. Neeraj Patil (BTech 2022) for their support in testing. 

## Third party code used
- [Fabric JS](https://fabricjs.com/): Javascript Canvas Library, SVG-to-Canvas (and canvas-to-SVG) Parser
- [FPDF](https://www.fpdf.org/): FPDF is a PHP class which allows to generate PDF files with pure PHP
- [FPDI](https://www.setasign.com/products/fpdi/about/): Collection of PHP classes facilitating developers to read pages from existing PDF documents and use them as templates in FPDF
- [Alpha PDF](http://www.fpdf.org/en/script/script74.php): The AlphaPDF is an extended class on FPDI that is used for setting transparency.
- [PDF JS](https://mozilla.github.io/pdf.js/): PDF.js is a Portable Document Format (PDF) viewer that is built with HTML5
- [PDF JS Annotations](https://github.com/RavishaHesh/PDFJsAnnotations): Wrapper for PDF JS to add annotations. We have done suitable modifications to this code.

## Icon
The icon <img width = "20" src="pix/icon.svg"> for this plugin is taken from [svgrepo.com](https://www.svgrepo.com/svg/501452/annotate).
```
LICENSE: MIT License
AUTHOR: instructure-ui
```
