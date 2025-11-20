export interface Task {
  id: number,
  description: string,
  isCompleted: boolean,
  priority: number
}

export interface TasksByDate {
  date: string,
  tasks: Task[]
}