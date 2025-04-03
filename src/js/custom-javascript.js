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
  // const oldFocus = document.querySelectorAll('.highlight');
  // oldFocus.forEach((focus) =>{
  //   focus.classList.remove('highlight')
  // })
  
  const focusLines = document.querySelectorAll(`[data-line="${lineNumber}"]`);
  focusLines.forEach((line) => {
    line.classList.add('highlight')
  })
  url_line_management(lineNumber, 'lines')
}

// Add/remove line ids while maintaining comma separation
function url_line_management(line, key) {
  const paramsString = window.location.search;
  const searchParams = new URLSearchParams(paramsString);
  
  if (searchParams.has('lines')) {
    const lines = searchParams.get('lines');
    let linesArray = lines.split(",").map(item => item.trim()).filter(item => item);
    
    if (linesArray.includes(line)) {
      // Remove the line if it exists
      linesArray = linesArray.filter(item => item !== line);
      const oldFocus = document.querySelectorAll(`[data-line="${line}"]`);
      oldFocus.forEach((focus) =>{
    		focus.classList.remove('highlight')
  		})

    } else {
      // Add the line if it doesn't exist
      linesArray.push(line);
    }
    
    if (linesArray.length > 0) {
      // Update with comma-separated values (not creating multiple parameters)
      searchParams.set("lines", linesArray.join(","));
    } else {
      // Remove the parameter if the array is empty
      searchParams.delete("lines");
    }
  } else {
    // Create the parameter if it doesn't exist
    searchParams.set("lines", line);
  }
  
  const newRelativePathQuery = '?' + searchParams.toString();
  // For testing (remove in production)
  // alert(newRelativePathQuery);
  
  // Update URL
  window.history.pushState({}, '', newRelativePathQuery);
  
  return newRelativePathQuery;
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

//highlight lines by url parameters
const paramsString = window.location.search;
const searchParams = new URLSearchParams(paramsString);
//alert(searchParams)
if(searchParams.has('lines')){
	const lines = searchParams.get('lines');
	//alert(lines)
	const lineArray = lines.split(",");
	lineArray.forEach(function(element) {
	  const focusLines = document.querySelectorAll(`[data-line="${element}"]`);
		  focusLines.forEach((highlight) => {
		    highlight.classList.add('highlight')
		  })
	});
}

