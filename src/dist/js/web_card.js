let webCard = createWebCard();

function createWebCard() {
  obj = {};

  obj.setup = async () => {
    const btnCopy = document.getElementById("btn-copy-link");
    if(btnCopy !== null){
      btnCopy.addEventListener("click", (event) => {
        console.log("clic");
        const url = btnCopy.dataset.href;
        obj.copiarContenido(url)
      });
    }

    const btnVcard = document.getElementById("btn-vCard");
    if(btnVcard !== null){
      btnVcard.addEventListener("click", (event) => {
        const vcard = btnVcard.dataset.vcard;
        console.log(vcard);
        this.createVCard(vcard);
      });
    }

    const zoomContent = document.getElementById("zoom-content");
    const webCardContent = document.getElementById("web-card-content");
    await sleep(1000);
    zoomContent.style.display = 'none';
    webCardContent.style.display = 'grid';
  };

  const sleep = ms => new Promise(r => setTimeout(r, ms));

  obj.copiarContenido = async (value) => {
    try {
      await navigator.clipboard.writeText(value);
      console.log("Contenido copiado al portapapeles");
      const copyNotification = document.querySelector("#copyNotification");
      const myToastNotification = bootstrap.Toast.getOrCreateInstance(copyNotification)
      myToastNotification.show();
      setTimeout(() => {
        myToastNotification.hide();
      }, 2000);
    } catch (err) {
      console.error("Error al copiar: ", err);
    }
  };

  this.downloadToFile = (content, filename, contentType)=> {
    const a = document.createElement('a');
    const file = new Blob([content], { type: contentType });
  
    a.href = URL.createObjectURL(file);
    a.download = filename;
    a.click();
  
    URL.revokeObjectURL(a.href);
  }

  this.createVCard = (vcard)=> {
    this.downloadToFile(vcard, 'vcard.vcf', 'text/vcard');
  }

  return obj;
}
