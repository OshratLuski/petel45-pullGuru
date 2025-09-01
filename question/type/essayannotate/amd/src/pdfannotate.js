/**
 * OnClick handlers are defined here
 * These functions will call functions in pdfannotate.js.
 *
 * @module     qtype_essayannotate/pdfannotate
 * @copyright  2024 IIT Palakkad
 * @copyright  based on work done by Ravisha Hesh {@link https://github.com/RavishaHesh/PDFJsAnnotations}
 * @license    http://opensource.org/licenses/mit-license The MIT License
 * PDFAnnotate v1.0.1
 * @author     Ravisha Hesh
 * @updatedby  Asha Jose and Parvathy S Kumar
 *             SerializePDF and SavePDF functions are modified.
 * @updatedby  Nideesh N, VM Sreeram
 *             Convert PDFAnnotate into AMD format, changed pdf workersrc to local version instead of cdn,
 *             removed call to onPageUpdated function
 */

define(['qtype_essayannotate/fabric', 'qtype_essayannotate/pdf', 'jquery'], function(fabric, pdfjsLib, $) {

var contextid, attemptid, filename, usageid, slot;
var PDFAnnotate = function(containerId, url, options = {}) {
    this.numberOfPages = 0;
    this.pagesRendered = 0;
    this.activeTool = 1; // 1 - Free hand, 2 - Text, 3 - Arrow, 4 - Rectangle
    this.fabricObjects = [];
    this.fabricObjectsData = [];
    this.color = 'rgb(0,0,0)';
    this.borderColor = 'rgb(0,0,0)';
    this.borderSize = 1;
    this.font_size = 16;
    this.activeCanvas = 0;
    this.containerId = containerId;
    this.url = url;
    this.pageImageCompression = options.pageImageCompression
    ? options.pageImageCompression.toUpperCase()
    : "NONE";
    this.textBoxText = 'Edit Text';
    this.highlightBoxWidth = 400;
    this.highlightBoxHeight = 50;
    this.highlightBoxOpacity = 0.3;
    this.freeDrawingBrushWidth = 2;
    var inst = this;
    pdfjsLib.GlobalWorkerOptions.workerSrc = '../amd/build/pdfworker.min.js';
    var loadingTask = pdfjsLib.getDocument(this.url);
    loadingTask.promise.then(function(pdf) {
        var scale = options.scale ? options.scale : 1.3;
        inst.numberOfPages = pdf.numPages;

        for (var i = 1; i <= pdf.numPages; i++) {

            pdf.getPage(i).then(function(page) { // Creating canvas and rendering pages in the canvas
                if (typeof inst.format === 'undefined' ||
                typeof inst.orientation === 'undefined') {
                    var originalViewport = page.getViewport({scale: 1});
                    inst.format = [originalViewport.width, originalViewport.height];
                    inst.orientation =
                    originalViewport.width > originalViewport.height ?
                        'landscape' :
                        'portrait';
                }

                var viewport = page.getViewport({scale: scale});
                var canvas = document.createElement('canvas');
                document.getElementById(inst.containerId).appendChild(canvas);
                canvas.className = 'pdf-canvas';
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                var context = canvas.getContext('2d');
                var renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                var renderTask = page.render(renderContext);

                renderTask.promise.then(function() {
                    $('.pdf-canvas').each(function(index, el) {
                        $(el).attr('id', 'page-' + (index + 1) + '-canvas');
                    });
                    inst.pagesRendered++;
                    if (inst.pagesRendered == inst.numberOfPages) { // Calling initFabric() after rendering the entire pages
                        inst.initFabric();
                    }
                });
            });
        }
    }, function(reason) { // eslint-disable-line no-unused-vars
    });

    this.initFabric = function() {
        var inst = this;
        let canvases = $('#' + inst.containerId + ' canvas');
        canvases.each(function(index, el) {
            var background = el.toDataURL("image/png");
            var fabricObj = new fabric.Canvas(el.id, {});
            inst.fabricObjects.push(fabricObj);
            fabricObj.setBackgroundImage(background, fabricObj.renderAll.bind(fabricObj));
            $(fabricObj.upperCanvasEl).click(function(event) {
                inst.activeCanvas = index;
                inst.fabricClickHandler(event, fabricObj);
            });
            fabricObj.on('after:render', function() {
                inst.fabricObjectsData[index] = fabricObj.toJSON();
                fabricObj.off('after:render');
            });

            if (index === canvases.length - 1 && typeof options.ready === 'function') {
                options.ready();
            }
        });
    };

    this.fabricClickHandler = function(event, fabricObj) {
        var inst = this;
        var activeObject = inst.fabricObjects[inst.activeCanvas].getActiveObject();
        var toolObj;

        if (inst.activeTool == 2) { // Text Box
            toolObj = new fabric.IText(inst.textBoxText, {
                left: event.clientX - fabricObj.upperCanvasEl.getBoundingClientRect().left,
                top: event.clientY - fabricObj.upperCanvasEl.getBoundingClientRect().top,
                fill: inst.color,
                fontSize: inst.font_size,
                lockRotation: true,
                lockScalingX: true,
                lockScalingY: true
            });
        } else if (inst.activeTool == 4) { // Highlight Box
            toolObj = new fabric.Rect({
                left: event.clientX - fabricObj.upperCanvasEl.getBoundingClientRect().left,
                top: event.clientY - fabricObj.upperCanvasEl.getBoundingClientRect().top,
                width: inst.highlightBoxWidth,
                height:  inst.highlightBoxHeight,
                fill: inst.color,
                opacity: inst.highlightBoxOpacity,
                lockRotation: true
            });
        } else if (inst.activeTool == 0) { // Select
            if (activeObject) {
                // Locking the rotation and scaling of free hand brush, if it is currently selected
                if (activeObject.get('type') == 'path') {
                    activeObject.set({
                        lockScalingX: true,
                        lockScalingY: true,
                        lockRotation: true});
                }
            }
        }

        // Change the current selected tool in the UI to Select if the active tool is highlight box or text
        if (inst.activeTool == 2 || inst.activeTool == 4) {
            var element = document.querySelector("#select");
            $(".tool-button.active").removeClass("active");
            $(element).addClass("active");
        }

        // Change the currently active tool to Select
        inst.activeTool = 0;
        if (toolObj) {
                fabricObj.add(toolObj);
        }
    };
};

PDFAnnotate.prototype.enableSelector = function() {
    var inst = this;
    inst.activeTool = 0;
    if (inst.fabricObjects.length > 0) {
        $.each(inst.fabricObjects, function(index, fabricObj) {
            fabricObj.isDrawingMode = false;
        });
    }


};

PDFAnnotate.prototype.enablePencil = function() {
    var inst = this;
    inst.activeTool = 1;
    if (inst.fabricObjects.length > 0) {
        $.each(inst.fabricObjects, function(index, fabricObj) {
            fabricObj.freeDrawingBrush.width = inst.freeDrawingBrushWidth; // Changed default brush size
            fabricObj.isDrawingMode = true;
        });
    }

};

PDFAnnotate.prototype.enableAddText = function() {
    var inst = this;
    inst.activeTool = 2;
    if (inst.fabricObjects.length > 0) {
        $.each(inst.fabricObjects, function(index, fabricObj) {
            fabricObj.isDrawingMode = false;
        });
    }

};

PDFAnnotate.prototype.enableRectangle = function() {
    var inst = this;
    inst.activeTool = 4;
    if (inst.fabricObjects.length > 0) {
        $.each(inst.fabricObjects, function(index, fabricObj) {
            fabricObj.isDrawingMode = false;
        });
    }
};

PDFAnnotate.prototype.deleteSelectedObject = function() {
    var inst = this;
    var activeObject = inst.fabricObjects[inst.activeCanvas].getActiveObject();
    if (activeObject) {
        if (confirm('Are you sure ?')) { // eslint-disable-line no-alert
            inst.fabricObjects[inst.activeCanvas].remove(activeObject);
        }
    }
};

// Updated by Asha Jose and Parvathy S Kumar
  PDFAnnotate.prototype.savePdf = function() {
    // Calling the serializePdf function
    this.serializePdf(function(string) {
      var value = JSON.stringify(JSON.parse(string), null, 4);

      var xmlhttp = new XMLHttpRequest(); // Creating an HTTP request instance
      xmlhttp.open("POST", "upload.php", true);
      xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      // Sending data to upload.php
      xmlhttp.send("data=" + value + "&contextid=" + contextid + "&attemptid=" + attemptid // Changed id to data
      + "&filename=" + filename + "&usageid=" + usageid + "&slot=" + slot);
      xmlhttp.onreadystatechange = function() {

        // Getting the response once upload.php finishes execution
        // The variable readyState will become 4 if the execution finishes
        if (this.status == 200 && this.readyState == 4) {
            showMessage(1);
        } else if (this.status != 200 && this.readyState == 4) {
            showMessage(0);
        }

        /**
         * Displays a message box with the given message.
         *
         * @param {int} message - 1 if the file has been saved, 0 otherwise.
         */
        function showMessage(message) {
            // Getting the message box
            var messageBox = document.getElementById("message-box-saved");
            if (message == 0) {
                messageBox = document.getElementById("message-box-not-saved");
            }

            // Showing the message box
            messageBox.classList.remove("hidden");

            // Automatically remove the message box after 3 seconds
            setTimeout(function() {
                messageBox.style.opacity = "0";
                setTimeout(function() {
                    document.getElementById("essayannotator-annotator").removeChild(messageBox);
                    window.close();
                    window.opener.location.reload();
                }, 500); // Fade-out transition time
            }, 3000); // Display time
        }
      };
    });
};


// Convert the Page Annotations to JSON data
PDFAnnotate.prototype.serializePdf = function(callback) {
    var inst = this;
    var pageAnnotations = [];
    // Initialising list of fabric objects for each page
    // Length of fabricObjects is the number of pages
    for (let i = 0; i < inst.fabricObjects.length; i++) {
      pageAnnotations.push([]);
    }

    // The function is invoked for each page  to iterate through the annotations
    inst.fabricObjects.forEach(function(fabricObject, index) {
      fabricObject.clone(function(fabricObjectCopy) {
        fabricObjectCopy.setBackgroundImage(null);
        fabricObjectCopy.setBackgroundColor('');
        if (fabricObjectCopy._objects.length !== 0) { // Checking if the page has any annotations
            for (var j = 0; j < fabricObjectCopy._objects.length; j++) { // Iterate through the list of annotations
                // Used to handle the translation of path object(free hand)
                if (fabricObjectCopy._objects[j].get('type') == 'path') {
                    var pathObj = fabricObjectCopy._objects[j];
                    var matrix = pathObj.calcTransformMatrix();
                    var pointsList = pathObj.path;
                    var length = Object.keys(pointsList).length;
                    var offsetX = pathObj.pathOffset.x;
                    var offsetY = pathObj.pathOffset.y;
                    for (var i = 0; i < length; i++) {
                        var point1 = new fabric.Point(pointsList[i][1], pointsList[i][2]);
                        var newPoints1 = fabric.util.transformPoint(point1, matrix);
                        pointsList[i][1] = newPoints1.x - offsetX;
                        pointsList[i][2] = newPoints1.y - offsetY;

                        if (i != 0 && i != length - 1) {
                            // First and Last elements in the pointsList have only a single set of coordinate
                            // All the other elements have 2 set of points
                            var point2 = new fabric.Point(pointsList[i][3], pointsList[i][4]);
                            var newPoints2 = fabric.util.transformPoint(point2, matrix);
                            pointsList[i][3] = newPoints2.x - offsetX;
                            pointsList[i][4] = newPoints2.y - offsetY;
                        }
                    }
                    // Copy transformed list of points to the path object
                    fabricObjectCopy._objects[j].path = pointsList;
                }
              }

            pageAnnotations[index].push(fabricObjectCopy);
        }

        if (index + 1 === inst.fabricObjects.length) {
          var data = {
            page_setup: {
              format: inst.format,
              orientation: inst.orientation,
            },
            pages: pageAnnotations,
          };
          callback(JSON.stringify(data)); // The serialized data is converted to JSON
        }
      });
    });
  };
// Updation ends

PDFAnnotate.prototype.setColor = function(color) {
    var inst = this;
    inst.color = color;
    $.each(inst.fabricObjects, function(index, fabricObj) {
        fabricObj.freeDrawingBrush.color = color;
    });
};

PDFAnnotate.prototype.setBorderColor = function(color) {
    var inst = this;
    inst.borderColor = color;
};

PDFAnnotate.prototype.setFontSize = function(size) {
    this.font_size = size;
};

return {
    PDFAnnotate: PDFAnnotate, // Exporting PDFAnnotate constructor
    init: function(contextId, attemptId, fileName, usageId, sloT) {
        contextid = contextId;
        attemptid = attemptId;
        filename = fileName;
        usageid = usageId;
        slot = sloT;
    }
};
});