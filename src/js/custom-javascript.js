// Add your JS customizations here


if(document.querySelectorAll('.line')){
	const allLines = document.querySelectorAll('.line');

allLines.forEach((line) => {
  line.addEventListener('click', () => {
    const lineNumber = line.dataset.line;
    highlightLines(lineNumber)
  });
});


function highlightLines(lineNumber){
  const oldFocus = document.querySelectorAll('.highlight');
  oldFocus.forEach((focus) =>{
    focus.classList.remove('highlight')
  })
  
  const focusLines = document.querySelectorAll(`[data-line="${lineNumber}"]`);
  focusLines.forEach((line) => {
    line.classList.add('highlight')
  })
}

function wrapWordsWithClass(selector, wordToWrap, wrapClass) {
  // Get all matching elements
  const elements = document.querySelectorAll(selector);
  elements.forEach(element => {
    // Get the HTML content
    let html = element.innerHTML;
    
    // Create a regex to match the word with word boundaries
    const regex = new RegExp(`\\b(${wordToWrap})\\b`, 'gi');
    
    // Replace the word with a wrapped version
    element.innerHTML = html.replace(regex, `<span class="${wrapClass}">$1</span>`);
  });
}


const buttons = document.querySelectorAll('button');

buttons.forEach((button) => {
  button.addEventListener('click', () => {
    const source = button.dataset.main;
    const find = button.dataset.find;
    const theClass = button.dataset.class;
    wrapWordsWithClass(source, find, theClass);
  });
})

}


