<?php

namespace Code16\Sharp\Dashboard;

use Code16\Sharp\Dashboard\Layout\DashboardLayoutRow;
use Code16\Sharp\Dashboard\Widgets\SharpGraphWidgetDataSet;
use Code16\Sharp\Dashboard\Widgets\SharpWidget;

abstract class SharpDashboard
{

    /**
     * @var bool
     */
    protected $dashboardBuilt = false;

    /**
     * @var bool
     */
    protected $layoutBuilt = false;

    /**
     * @var array
     */
    protected $widgets = [];

    /**
     * @var array
     */
    protected $graphWidgetDataSets = [];

    /**
     * @var array
     */
    protected $panelWidgetsData = [];

    /**
     * @var array
     */
    protected $rows = [];

    /**
     * Add a widget.
     *
     * @param SharpWidget $widget
     * @return $this
     */
    protected function addWidget(SharpWidget $widget)
    {
        $this->widgets[] = $widget;
        $this->dashboardBuilt = false;

        return $this;
    }

    /**
     * Add a new row with a single widget.
     *
     * @param string $widgetKey
     * @return $this
     */
    protected function addFullWidthWidget(string $widgetKey)
    {
        $this->layoutBuilt = false;

        $this->addRow(function(DashboardLayoutRow $row) use ($widgetKey) {
            $row->addWidget(12, $widgetKey);
        });

        return $this;
    }

    /**
     * Add a new row.
     *
     * @param \Closure $callback
     * @return $this
     */
    protected function addRow(\Closure $callback)
    {
        $row = new DashboardLayoutRow();

        $callback($row);

        $this->rows[] = $row;

        return $this;
    }

    public function widgets()
    {
        $this->checkDashboardIsBuilt();

        return collect($this->widgets)->map(function(SharpWidget $widget) {
            return $widget->toArray();
        })->keyBy("key")->all();
    }

    /**
     * Return the dashboard widgets layout.
     *
     * @return array
     */
    function widgetsLayout(): array
    {
        if(!$this->layoutBuilt) {
            $this->buildWidgetsLayout();
            $this->layoutBuilt = true;
        }

        return [
            "rows" => collect($this->rows)->map(function(DashboardLayoutRow $row) {
                return $row->toArray();
            })->all()
        ];
    }

    /**
     * Return data, as an array.
     *
     * @return array
     */
    function data(): array
    {
        $this->buildWidgetsData();

        // First, graph widgets dataSets
        $data = collect($this->graphWidgetDataSets)
            ->map(function(array $dataSets, string $key) {
                $dataSetsValues = collect($dataSets)->map->toArray();

                return [
                    "key" => $key,
                    "datasets" => $dataSetsValues->map(function($dataSet) {
                        return array_except($dataSet, "labels");
                    })->all(),
                    "labels" => $dataSetsValues->first()["labels"]
                ];
            });

        // Then, panel widgets data
        return $data->merge(
            collect($this->panelWidgetsData)->map(function($value, $key) {
                return [
                    "key" => $key,
                    "data" => $value
                ];
            })
        )->all();
    }

    /**
     * @param string $graphWidgetKey
     * @param SharpGraphWidgetDataSet $dataSet
     * @return $this
     */
    protected function addGraphDataSet(string $graphWidgetKey, SharpGraphWidgetDataSet $dataSet)
    {
        $this->graphWidgetDataSets[$graphWidgetKey][] = $dataSet;

        return $this;
    }

    /**
     * @param string $panelWidgetKey
     * @param array $data
     * @return $this
     */
    protected function setPanelData(string $panelWidgetKey, array $data)
    {
        $this->panelWidgetsData[$panelWidgetKey] = $data;

        return $this;
    }

    private function checkDashboardIsBuilt()
    {
        if (!$this->dashboardBuilt) {
            $this->buildWidgets();
            $this->dashboardBuilt = true;
        }
    }

    /**
     * Build dashboard's widget using ->addWidget.
     */
    protected abstract function buildWidgets();

    /**
     * Build dashboard's widgets layout.
     */
    protected abstract function buildWidgetsLayout();

    /**
     * Build dashboard's widgets data, using ->addGraphDataSet and ->setPanelData
     */
    protected abstract function buildWidgetsData();
}