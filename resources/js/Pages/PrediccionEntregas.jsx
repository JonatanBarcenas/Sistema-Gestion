import React, { useState, useEffect } from 'react';
import { Line } from 'react-chartjs-2';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';
import axios from 'axios';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

const PrediccionEntregas = () => {
    const [datosHistoricos, setDatosHistoricos] = useState([]);
    const [prediccion, setPrediccion] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [filtroMeses, setFiltroMeses] = useState(6);

    useEffect(() => {
        cargarDatosHistoricos();
    }, [filtroMeses]);

    const cargarDatosHistoricos = async () => {
        try {
            const response = await axios.get('/api/proyectos/historicos', {
                params: { meses: filtroMeses }
            });
            setDatosHistoricos(response.data);
        } catch (error) {
            setError('Error al cargar datos históricos');
            console.error(error);
        }
    };

    const opcionesGrafico = {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Predicción de Fechas de Entrega',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `${context.dataset.label}: ${context.parsed.y} días`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Duración (días)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Fecha'
                }
            }
        }
    };

    const datosGrafico = {
        labels: datosHistoricos.map(d => new Date(d.fecha_inicio).toLocaleDateString()),
        datasets: [
            {
                label: 'Duración Real',
                data: datosHistoricos.map(d => d.duracion_real),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
            },
            {
                label: 'Duración Estimada',
                data: datosHistoricos.map(d => d.duracion_estimada),
                borderColor: 'rgb(255, 99, 132)',
                borderDash: [5, 5],
                tension: 0.1,
            },
            ...(prediccion ? [{
                label: 'Predicción',
                data: Array(datosHistoricos.length).fill(prediccion.duracionEstimada),
                borderColor: 'rgb(54, 162, 235)',
                borderDash: [5, 5],
                tension: 0.1,
            }] : [])
        ],
    };

    const generarPrediccion = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await axios.post('/api/prediccion/generar', {
                datosHistoricos
            });
            setPrediccion(response.data.prediccion);
        } catch (error) {
            setError('Error al generar predicción');
            console.error(error);
        }
        setLoading(false);
    };

    return (
        <div className="p-6">
            <div className="bg-white rounded-lg shadow-lg p-6">
                <div className="flex justify-between items-center mb-6">
                    <h2 className="text-2xl font-bold">Predicción de Fechas de Entrega</h2>
                    <div className="flex items-center space-x-4">
                        <select
                            value={filtroMeses}
                            onChange={(e) => setFiltroMeses(Number(e.target.value))}
                            className="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        >
                            <option value={3}>Últimos 3 meses</option>
                            <option value={6}>Últimos 6 meses</option>
                            <option value={12}>Último año</option>
                            <option value={24}>Últimos 2 años</option>
                        </select>
                        <button
                            onClick={generarPrediccion}
                            disabled={loading}
                            className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                        >
                            {loading ? 'Generando predicción...' : 'Generar Predicción'}
                        </button>
                    </div>
                </div>

                {error && (
                    <div className="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        {error}
                    </div>
                )}

                <div className="h-96 mb-6">
                    <Line options={opcionesGrafico} data={datosGrafico} />
                </div>

                {prediccion && (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div className="bg-gray-50 p-4 rounded-lg">
                            <h3 className="text-xl font-semibold mb-3">Resumen de Predicción</h3>
                            <div className="space-y-2">
                                <p><span className="font-medium">Duración Estimada:</span> {Math.round(prediccion.duracionEstimada)} días</p>
                                <p><span className="font-medium">Intervalo de Confianza:</span></p>
                                <ul className="list-disc pl-5">
                                    <li>Inferior: {Math.round(prediccion.intervaloConfianza.inferior)} días</li>
                                    <li>Superior: {Math.round(prediccion.intervaloConfianza.superior)} días</li>
                                </ul>
                                <p><span className="font-medium">Tendencia:</span> {
                                    prediccion.tendencia > 0 ? 'Aumento' : 
                                    prediccion.tendencia < 0 ? 'Disminución' : 
                                    'Estable'
                                } en la duración de los proyectos</p>
                            </div>
                        </div>
                        <div className="bg-gray-50 p-4 rounded-lg">
                            <h3 className="text-xl font-semibold mb-3">Recomendaciones</h3>
                            <ul className="list-disc pl-5 space-y-2">
                                {prediccion.recomendaciones.map((rec, index) => (
                                    <li key={index}>{rec}</li>
                                ))}
                            </ul>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default PrediccionEntregas; 