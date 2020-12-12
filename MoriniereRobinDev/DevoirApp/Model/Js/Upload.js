"use strict";

let fileUpload = document.getElementById('fileUpload');
if(fileUpload != null){
    console.log(fileUpload);
    fileUpload.addEventListener('change', function (e) {    // 'drop'
        let fichier = fileUpload.files[0];
     	let reader = new FileReader();
        console.log("coucou");

     	reader.addEventListener('load', function (e) {
     		let arr = new Int8Array(reader.result);
     		arr[0] = 65;

     		let blob = new Blob([arr.buffer]);
     		let blobreader = new FileReader();
     		blobreader.addEventListener('load', function (e) {
     			// console.log('Blob Reader : ', blobreader.result);
     		});
     		blobreader.readAsText(blob);
     	});
     	reader.readAsArrayBuffer(fichier);
     });


    let submit = document.getElementById("subFileUpload");
    submit.addEventListener('click', function (e) {
    	let xhr = new XMLHttpRequest();
    	xhr.open('POST', 'index.php?obj=pdf&action=upload');
        xhr.responseType = 'json';

        let fichier = fileUpload.files[0];

    	let data = new FormData();
    	data.append('mon-fichier', fichier);

        console.log('Fichier : ', fichier);
        console.log('Form Data : ', data);
        console.log('xhr response : ', xhr.response);

    	xhr.addEventListener('load', function(e) {
            console.log('xhr response load : ', xhr.response);
    	});

     	xhr.upload.addEventListener('progress', function (e) {
     		console.log('Progress Bar : ', e);
            document.getElementById("progressBar").value = e.loaded  / e.total;
     	});

        xhr.send(data);
    });
}


let dropFileForm = document.getElementById("dropFileForm");
let dropFileDiv = document.getElementById("dropFileDiv");
let fileLabelText = document.getElementById("fileLabelText");
let uploadStatus = document.getElementById("uploadStatus");
let fileInput = document.getElementById("fileInput");
let droppedFiles;

function overrideDefault(event){
    event.preventDefault();
    event.stopPropagation();
}

console.log(dropFileDiv);

function fileHover(){
    dropFileDiv.classList.add("fileHover");
}

function fileHoverEnd(){
    dropFileDiv.classList.remove("fileHover");
}

function addFiles(event){
    droppedFiles = event.target.files || event.dataTransfer.files;
    showFiles(droppedFiles);
}

function showFiles(files){
    if(files.length > 1){
        fileLabelText.innerText = files.length + " files selected";
    }
    else{
        fileLabelText.innerText = files[0].name;
    }
}

function uploadFiles(event){
    event.preventDefault();
    changeStatus("Uploading...");

    let formData = new FormData();

    for (let i = 0, file; (file = droppedFiles[i]); i++){
        formData.append(fileInput.name, file, file.name);
    }

    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(data){
        //handle server response and change status of
        //upload process via changeStatus(text)
        console.log(xhr.response);
    };
    xhr.open(dropFileForm.method, dropFileForm.action, true);
    console.log(formData);
    xhr.send(formData);
}

function changeStatus(text){
    uploadStatus.innerText = text;
}


// let submit = document.getElementById("subFileUpload");
// submit.addEventListener('click', async function (e) {
//     let formData = new FormData();
//     formData.append("file", fileUpload.files[0]);
//     await fetch('/index.php?obj=pdf&action=upload', {
//         method: "POST",
//         body: formData
//     });
//     alert('The file has been uploaded successfully.');
// });

// async function uploadFile() {
//     let formData = new FormData();
//     formData.append("file", fileUpload.files[0]);
//     await fetch('/index.php?obj=pdf&action=upload', {
//         method: "POST",
//         body: formData
//     });
//     alert('The file has been uploaded successfully.');
// }
