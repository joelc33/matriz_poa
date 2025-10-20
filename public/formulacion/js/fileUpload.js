/**
 * @param {domElement} input - The input element
 * @param {string} typeData - The type of data to be return in the event object. 
 */
function loadFileFromInput(input,typeData) {
    var reader,
        fileLoadedEvent,
        files = input.files;

    if (files && files[0]) {
        reader = new FileReader();

        reader.onload = function (e) {
            fileLoadedEvent = new CustomEvent('fileLoaded',{
                detail:{
                    data:reader.result,
                    file:files[0]  
                },
                bubbles:true,
                cancelable:true
            });
            input.dispatchEvent(fileLoadedEvent);
        }
        switch(typeData) {
            case 'arraybuffer':
                reader.readAsArrayBuffer(files[0]);
                break;
            case 'dataurl':
                reader.readAsDataURL(files[0]);
                break;
            case 'binarystring':
                reader.readAsBinaryString(files[0]);
                break;
            case 'text':
                reader.readAsText(files[0]);
                break;
        }
    }
}
function fileHandler (e) {
    var data = e.detail.data,
        fileInfo = e.detail.file;

    img.src = data;
}
var input = document.getElementById('inputId'),
    img = document.getElementById('imgId');

input.onchange = function (e) {
    loadFileFromInput(e.target,'dataurl');
};

input.addEventListener('fileLoaded',fileHandler)
