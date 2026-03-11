export function addTask(text) {
    const tasks = JSON.parse (localStorage.getItem("tasks"));
    const newTask = {
        id: tasks.length,
        text: text
    }
    const newTaskList = [...tasks, newTask]
    localStorage.setItem("taks", JSON.stringify(newTaskList));
}

export function deleteTask(id){
    const tasks = localStorage.getItem("tasks");
    const filteredTasks = tasks.filter(task => task.id !== id);
    localStorage.setItem("tasks", JSON.stringify (filteredTasks));
}
