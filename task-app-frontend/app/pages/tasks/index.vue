<template>
  <div>
    <NuxtLayout name="default" v-model:search="getTaskForm.search">
      <SidebarProvider>
        <AppSidebar
          :task-dates="taskDates"
          v-model:active-date="getTaskForm.date"
        />
        <div class="h-full w-full pt-6 px-4 pb-4">
          <div v-if="isFetchingTasks" class="h-full flex items-center justify-center ">
            <Spinner class="w-16 h-16" />
          </div>
          <div v-else-if="tasks.length > 0" class="h-full flex flex-col items-center">
            <div class="flex w-full max-w-3xl grow flex-col justify-start">
              <draggable
                v-model="draggableTasks"
                item-key="id"
                animation="200"
                ghost-class="opacity-50"
                @end="debouncedReorderTask"
                class="space-y-4"
              >
                <template #item="{ element: task }">
                  <div
                    class="group flex items-center justify-between px-4 py-3 bg-white rounded-xl border border-neutral-200 transition-all hover:bg-neutral-100"
                  >
                    <div class="flex items-center space-x-4 grow">
                      <div
                        :class="cn(
                          `size-5 rounded-full border border-gray-300 shrink-0 cursor-pointer bg-white flex justify-center items-center`,
                          task.isCompleted ? 'bg-black/90' : ''
                        )"
                        @click="handleUpdateTaskStatus(task.id, !task.isCompleted)"
                      >
                        <Check :size="12" color="white"/>
                      </div>

                      <!-- Task description / editing -->
                      <div class="grow">
                        <div v-if="editingTaskId === task.id" class="w-full flex items-center space-x-2">
                          <InputGroupInput
                            v-model="editedDescription"
                            placeholder="Edit task"
                            class="flex-1 px-0"
                          />

                          <div class="flex space-x-2">
                            <Button class="py-1" size="sm" @click="() => saveEditing(task)" :loading="isSaving">Save</Button>
                            <Button class="py-1" size="sm" variant="secondary" @click="cancelEditing">Cancel</Button>
                          </div>
                        </div>

                        <p
                          v-else
                          :class="cn('text-gray-700 grow leading-tight', task.isCompleted ? 'line-through' : '')"
                          @dblclick="() => startEditing(task)"
                        >
                          {{ task.description }}
                        </p>
                      </div>
                    </div>
                    <div class="shrink-0 ml-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                      <Trash2Icon
                        class="size-5 text-gray-400 cursor-pointer hover:text-red-500 transition-colors"
                        @click="openDeleteConfirmation(task.id)"
                      />
                    </div>
                  </div>
                </template>
              </draggable>
            </div>
            <div class="flex w-full max-w-3xl">
              <InputGroup>
                <InputGroupTextarea v-model="addTaskForm.description" placeholder="What else do you need to do?" class="wrap-break-word break-all min-h-12" row="1"/>
                <InputGroupAddon align="inline-end">
                  <InputGroupButton
                    variant="default"
                    class="opacity-100! text-white rounded-full"
                    size="icon-xs"
                    align-content-end
                    type="submit"
                    @click="handleAddTask"
                    :disabled="addTaskForm.description == '' || isAddingTask"
                    :class="{
                      'hover:cursor-pointer!': addTaskForm.description && !isAddingTask,
                      'opacity-50! hover:cursor-not-allowed!': addTaskForm.description == '' || isAddingTask
                    }"
                  >
                    <ArrowUp v-if= "!isAddingTask"class="size-4" />
                    <Spinner v-else= "!isAddingTask"class="size-4" />
                    <span class="sr-only">Send</span>
                  </InputGroupButton>
                </InputGroupAddon>
              </InputGroup>
            </div>
          </div>
          <div v-else class="h-full flex flex-col items-center justify-center">
            <div class="lg:max-w-7/12 w-full">
              <div class="text-2xl font-bold mb-2 text-center">What do you have in mind?</div>
              <InputGroup>
                <InputGroupTextarea v-model="addTaskForm.description" placeholder="Write the task you plan to do today here..." class="wrap-break-word break-all"/>
                <InputGroupAddon align="block-end" class="justify-end hover:cursor-auto">
                  <InputGroupButton
                    variant="default"
                    class="opacity-100! text-white rounded-full"
                    size="icon-xs"
                    align-content-end
                    type="submit"
                    :disabled="addTaskForm.description == '' || isAddingTask"
                    :class="{
                      'hover:cursor-pointer!': addTaskForm.description && !isAddingTask,
                      'opacity-50! hover:cursor-not-allowed!': addTaskForm.description == '' || isAddingTask
                    }"
                    @click="handleAddTask"
                  >
                    <ArrowUp v-if="!isAddingTask"class="size-4" />
                    <Spinner v-else="!isAddingTask"class="size-4" />
                    <span class="sr-only">Send</span>
                  </InputGroupButton>
                </InputGroupAddon>
              </InputGroup>
            </div>
          </div> 
        </div>
      </SidebarProvider>
    </NuxtLayout>
  </div>

  <!-- Delete confirmation -->
  <Dialog v-model:open="isDeleteDialogOpen">
    <!-- Dialog content -->
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Delete confirmation</DialogTitle>
        <DialogDescription>
          Are you sure you want to delete this task?
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <DialogClose as-child>
          <Button variant="outline">Cancel</Button>
        </DialogClose>
        <Button class="bg-red-500 hover:bg-red-500/70" @click="handleDeleteTask()">Delete</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ArrowUp, Check, Trash2Icon } from "lucide-vue-next";
import { taskRepository, type AddTaskForm, type GetAllTasksForm} from "~/repositories/taskRepository";
import dayjs from 'dayjs'
import type { Task } from "~/types/task";
import { cn } from "@/lib/utils"
import { InputGroup, InputGroupAddon, InputGroupButton, InputGroupInput, InputGroupTextarea } from '@/components/ui/input-group'
import { Button } from '@/components/ui/button'
import { Spinner } from '@/components/ui/spinner'
import SidebarProvider from '@/components/ui/sidebar/SidebarProvider.vue'
import AppSidebar from '@/components/AppSidebar.vue'
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { useDebounceFn, watchDebounced } from "@vueuse/core";
import draggable from 'vuedraggable'

definePageMeta({
  layout: false,
})

onMounted(async() => {
  await handleGetAllTaskDates()
})


// ===== API and Repository =====
const { $api } = useNuxtApp()
const taskRepo = taskRepository($api)


// ===== Get all Task by date=====
const tasks = reactive<Task[]>([])
const draggableTasks = computed({
  get: () => tasks,
  set: (newOrder) => {
    tasks.splice(0, tasks.length, ...newOrder)
  }
})
const isFetchingTasks = ref(false)
const getTaskForm = reactive<GetAllTasksForm>({
  search: '',
  date: dayjs().format('YYYY-MM-DD')
})
const currentRequestId = ref(0)
const handleGetAllTasks = async() => {
  const requestId = ++currentRequestId.value
  try {
    const { data } = await taskRepo.get(getTaskForm)

    // Only update tasks if this request is still the latest
    if (requestId === currentRequestId.value) {
      tasks.splice(0, tasks.length, ...(data ?? []))
    }
  } catch (error) {
    console.error('Failed to load tasks:', error)
  } 
}
watchDebounced(
  getTaskForm,
  async() => {
    isFetchingTasks.value = true
    await handleGetAllTasks()
    isFetchingTasks.value = false
  },
  { immediate: true, debounce: 500, maxWait: 1000, deep: true },
)


// ===== Adding new tasks =====
const addTaskForm = reactive<AddTaskForm>({
  description: ''
})
const isAddingTask = ref(false)
const handleAddTask = async() => {
  try {
    isAddingTask.value = true
    const { data } = await taskRepo.post(addTaskForm)
    if (data) {
      tasks.push(data)
    }
  } catch (error) {
    console.error('Failed to add task:', error)
  } finally{
    isAddingTask.value = false
    addTaskForm.description = ''
  }
}


// ===== Fetching all tasks dates =====
const taskDates = ref<string[]>([dayjs().format('YYYY-MM-DD')])
const handleGetAllTaskDates = async() => {
  try {
    const { data } = await taskRepo.getDates()
    taskDates.value = data ?? []
    // Add today if not present
    const today = dayjs().format('YYYY-MM-DD')
    if (!taskDates.value.includes(today)) {
      taskDates.value.unshift(today) // add at the start
    }
  } catch (error) {
    console.error('Failed to load tasks: ', error)
  }
}


// ===== Completing a task =====
const handleUpdateTaskStatus = async(id: number, isCompleted: boolean) => {
  const taskToUpdate = tasks.find((t) => t.id == id)
  try {
    // Update the data
    if(taskToUpdate){
      taskToUpdate.isCompleted = isCompleted
    }
    const { data } = await taskRepo.patch({
      id: id,
      isCompleted: isCompleted
    })
    if(data && taskToUpdate){
      taskToUpdate.isCompleted = isCompleted
    }
  } catch (error) {
    console.error('Failed to load tasks: ', error)
    // Revert to its original status when an error occurs
    if(taskToUpdate){
      taskToUpdate.isCompleted = !isCompleted
    }
  } finally {

  }
}


// ===== Updating a task description =====
const editingTaskId = ref<number | null>(null)
const editedDescription = ref<string>('')
const isSaving = ref(false)
const startEditing = (task: Task) => {
  editingTaskId.value = task.id
  editedDescription.value = task.description
}
const saveEditing = async (task: Task) => {
  const originalTaskDescription = task.description
  if (editedDescription.value !== task.description) {
    isSaving.value = true
    try{
      await handleUpdateTaskDescription(task.id, editedDescription.value)
    }
    catch(err){
      console.log('An unexpected error occured: ', err)
      // Revert the text
      task.description = originalTaskDescription
    }
  }
  cancelEditing()
}
const cancelEditing = () => {
  editingTaskId.value = null
  editedDescription.value = ''
  isSaving.value = false
}
const handleUpdateTaskDescription = async(id: number, description: string) => {
  try {
    const { data } = await taskRepo.patch({
      id: id,
      description: description
    })

    // Update the data
    const taskToUpdate = tasks.find((t) => t.id == id)
    if(taskToUpdate && data){
      taskToUpdate.description = data.description
    }
  } catch (error) {
    console.error('Failed to load tasks: ', error)
  }
}


// ===== Deleting a task =====
const isDeleteDialogOpen = ref(false)
const taskIdToDelete = ref<number | undefined>(undefined)
const openDeleteConfirmation = async(id: number) => {
  isDeleteDialogOpen.value = true
  taskIdToDelete.value = id
}
const handleDeleteTask = async() => {
  isDeleteDialogOpen.value = false
  if(!taskIdToDelete.value)
    return
  // Find the index of the task
  const index = tasks.findIndex(i => i.id === taskIdToDelete.value);
  if (index === -1) return; // Task not found

  // Save a copy of the task in case deletion fails
  const removedTask = tasks[index];
  if (!removedTask) return;
  
  try {
    // Remove the task locally
    tasks.splice(index, 1);

    await taskRepo.delete(taskIdToDelete.value)
  } catch (error) {
    console.error('Failed to load tasks: ', error)
    // Refresh task
    await handleGetAllTasks()
  }
}


// ===== Reordering Tasks =====
const reorderTask = async () => {
  try {
    const orderedIds = tasks.map(task => task.id)
    const { data } =await taskRepo.patchOrder({
      orderedIds: orderedIds,
      date: getTaskForm.date
    })
    tasks.splice(0, tasks.length, ...(data ?? []))
  } catch (err) {
    console.error("Error saving order", err)
    // Refresh task
    await handleGetAllTasks()
  }
}
const debouncedReorderTask = useDebounceFn(() => {
  reorderTask()
}, 1000)

</script>

<style scoped>
</style>  
