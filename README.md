# timepunches


## Instructions

The software load three files: location, user and time punches.

The idea is to create a page where we can query all time punches of one selected user, also the total of workday, wage, etc.

The software can be upgraded by better coding some sections. I tried to demonstrate the use of objects, arrays, javascript requests, date and time methods, etc.

All works on the same file, we just manipulate the GET request to show only time punches or the list of users to be selected.


## Thinks to make

- With memcache we can improve the response time of the time punch query.
- Separate the classes on different files will help to manipulate the totals for each location, weeklog and daylog.
- Create a better class to front-end, separatly from back-end, can make easier to make updates on the code.
- There is a block of javascript code. This block can be move to a separate javascript file.
- I don't put much hour/work on css coding. I think that this step is more exclusive to each case. Also, this doesn't affect the features.
- The JSON files can be store on another location. For this example I use a simples local storage.