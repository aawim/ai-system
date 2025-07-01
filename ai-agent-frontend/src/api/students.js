import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
});

export const fetchStudents = () => api.get('/students');
export const createStudent = (data) => api.post('/students', data);
export const evaluateStudent = (id) => api.post(`/students/${id}/evaluate`);
