function toggleSkills(category) {
    const skillsList = document.getElementById(category);
    if (skillsList.style.display === 'none' || skillsList.style.display === '') {
        skillsList.style.display = 'block';
    } else {
        skillsList.style.display = 'none';
    }
}


function limitfunc() {
    let allSkills = document.querySelectorAll('.skills');
    let selected = 0;

    for (let count = 0; count < allSkills.length; count++) {
        if (allSkills[count].checked) {
            selected += 1;
        }
    }

    
    if (selected > 3) {
        
        const lastChecked = [...allSkills].reverse().find(skill => skill.checked);
        if (lastChecked) {
            lastChecked.checked = false; 
        }
        document.querySelector('#invalid').innerText = "You can only choose 3 skills!";
        return false; 
    } else {
        document.querySelector('#invalid').innerText = "";
    }
}

//check if there's shorter way (note: if this will change there will be several adjustments)
const skillMapping = {
    1: "Welding",
    2: "Concrete Work",
    3: "Roofing",
    4: "Carpentry",
    5: "Masonry",
    6: "Painting",
    7: "Tiling",
    8: "Demolition",
    9: "Shoveling",
    10: "Sanding",
    11: "Laundry",
    12: "Derusting",
    13: "Gutter Cleaning",
    14: "Pressure Washing",
    15: "Tree Trimming",
    16: "Pest Control",
    17: "Sweeping",
    18: "Surface Scrubbing",
    19: "Mopping",
    20: "Vacuuming",
    21: "Dishwashing",
    22: "Food Stocking",
    23: "Food Preparation",
    24: "Serving",
    25: "Table Bussing",
    26: "Order Taking",
    27: "Delivering",
    28: "Cooking",
    29: "Bagging",
    30: "Meat Cutting",
    31: "Leaf Blowing",
    32: "Planting",
    33: "Pruning & Trimming",
    34: "Weeding",
    35: "Watering",
    36: "Copywriting",
    37: "Graphic Design",
    38: "Video Editing",
    39: "Web Design",
    40: "Photography"
};

function updateTextbox() {
   
    let checkboxes = document.querySelectorAll('input[type="checkbox"]');
    
    let selectedSkills = [];
    
    checkboxes.forEach(function(checkbox) {
        if (checkbox.checked) {
          
            selectedSkills.push(skillMapping[checkbox.value]);
        }
    });
    
    document.getElementById('selected-skill1').value = selectedSkills[0] || ''; 
    document.getElementById('selected-skill2').value = selectedSkills[1] || '';
    document.getElementById('selected-skill3').value = selectedSkills[2] || '';
}
