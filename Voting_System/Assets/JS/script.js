let bodyEl = document.querySelector('body');
let rainOn = document.querySelector('.switch-1');
let rainOff = document.querySelector('.switch-2');
let CanvaEl =  document.querySelector('canvas');
rainOff.style.display = 'none';

rainOn.addEventListener('click', (e) =>{
    CanvaEl.style.display = 'none';
    rainOn.style.display ="none";
    rainOff.style.display ="block";
});

rainOff.addEventListener('click', (e) =>{
    CanvaEl.style.display = 'block';
    rainOn.style.display ="block";
    rainOff.style.display ="none";
});