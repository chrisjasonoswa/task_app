import type {$Fetch, NitroFetchRequest } from 'nitropack'
import type { ApiResponse } from "~/types/apiResponse"
import type { Task } from "~/types/task"

export interface GetAllTasksForm {
  search: string
  date: string
}

export interface AddTaskForm {
  description: string
}

export interface UpdateTaskForm {
  id: number
  isCompleted?: boolean
  description?: string
}

export interface UpdateTaskOrderForm {
  orderedIds: number[],
  date: string
}


const BASE_RESOURCE = '/tasks'
export const taskRepository = <T>(fetch: $Fetch<T, NitroFetchRequest>) => ({
  async get(formData: GetAllTasksForm){
    return fetch<ApiResponse<Task[]>>(BASE_RESOURCE, {
      params: {
        search: formData.search,
        date: formData.date
      },
    })
  },

  async patch(formData: UpdateTaskForm){
    const { id, ...payload } = formData
    return fetch<ApiResponse<Task>>(BASE_RESOURCE + `/${id}`, {
      method: 'PATCH',
      body: {
        is_completed: payload.isCompleted,
        description: payload.description,
      }
    })
  },

  async post(formData: AddTaskForm){
    return fetch<ApiResponse<Task>>(BASE_RESOURCE, {
      method: 'POST',
      body: formData
    })
  },
  
  async delete(id: number){
    return fetch<ApiResponse<Task>>(BASE_RESOURCE + `/${id}`, {
      method: 'DELETE',
    })
  },

  async getDates(){
    return fetch<ApiResponse<string[]>>(BASE_RESOURCE + '/dates')
  },

  async patchOrder(formData: UpdateTaskOrderForm){
    return fetch<ApiResponse<Task[]>>(BASE_RESOURCE + "/order", {
      method: 'PATCH',
      body: {
        ordered_ids: formData.orderedIds,
        date: formData.date
      }
    })
  },
})