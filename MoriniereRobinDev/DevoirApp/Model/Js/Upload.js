"use strict";

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

function fileHover(){
    dropFileDiv.classList.add("fileHover");
}

function fileHoverEnd(){
    dropFileDiv.classList.remove("fileHover");
}

function addFiles(event){
    console.log('addFiles');
    droppedFiles = event.target.files || event.dataTransfer.files;
    showFiles(droppedFiles);
}

function showFiles(files){
    console.log('showFiles');
    if(files.length > 1){
        fileLabelText.innerText = files.length + " files selected";
    }
    else{
        fileLabelText.innerText = files[0].name;
    }
}

function changeStatus(text){
    console.log('changeStatus');
    uploadStatus.innerText = text;
}

function uploadFiles(event){
    console.log(droppedFiles);
    if(typeof droppedFiles === 'undefined'){
        return false;
    }

    console.log('uploadFiles');
    event.preventDefault();  // Stop redirect to PHP
    changeStatus("Uploading...");

    console.log("SubmitXHR");
    let xhr = new XMLHttpRequest();
    xhr.open('POST', 'https://dev-21606393.users.info.unicaen.fr/M1/Tw4/Projet/MoriniereRobinDev/index.php?obj=pdf&action=upload');
    xhr.responseType = 'json';

    let redirect;
    if(droppedFiles.length > 1){
        redirect = 'ajaxUploadMultipleSucces';
    }
    else{
        let file = droppedFiles[0].name;
        let fileWithoutExtension = file.split('.');
        redirect = 'ajaxUploadSucces&id=' + fileWithoutExtension[0];
    }

    let formData = new FormData();
    for(let i = 0; i < droppedFiles.length; i++) {
        formData.append(i, droppedFiles[i])
    }

    xhr.addEventListener('load', function(e) {
        console.log('xhr response load : ', xhr.response);
    });

    xhr.upload.addEventListener('progress', function (e) {
        console.log('Progress Bar : ', e);
        document.getElementById("progressBar").value = e.loaded  / e.total;
    });

    xhr.onreadystatechange = function(){ // listen for state changes
        if(xhr.readyState == 4 && xhr.status == 200) { // when completed we can move away
            window.location = "https://dev-21606393.users.info.unicaen.fr/M1/Tw4/Projet/MoriniereRobinDev/index.php?obj=pdf&action=" + redirect;
        }
    }
    xhr.send(formData);
}
