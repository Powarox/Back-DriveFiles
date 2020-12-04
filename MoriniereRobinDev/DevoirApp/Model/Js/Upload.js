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
